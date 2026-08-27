"""
Simple example to read the ESRI ASCII grid DTM (dtm_example.asc) and the
volume history CSV, then generate a sequence of 3D surface frames that vary
slightly per timestep to simulate an iterative 3D image (e.g., DTM + water
level or deformation over time).

Usage:
  python visualize_dtm_example.py

Dependencies: numpy, matplotlib, pandas (install with pip if necessary)
"""
import os
import math
import numpy as np
import matplotlib.pyplot as plt
from matplotlib import cm
from mpl_toolkits.mplot3d import Axes3D
import pandas as pd

BASE_DIR = os.path.dirname(__file__)
DTM_PATH = os.path.join(BASE_DIR, 'dtm_example.asc')
VOLUME_CSV = os.path.join(BASE_DIR, 'volume_history_example.csv')
FRAMES_DIR = os.path.join(BASE_DIR, 'frames')
os.makedirs(FRAMES_DIR, exist_ok=True)


def read_esri_ascii(path):
    """Return (grid, x_coords, y_coords, header)"""
    with open(path, 'r') as f:
        header = {}
        for _ in range(6):
            line = f.readline()
            k, v = line.split()
            header[k.lower()] = float(v)
        ncols = int(header['ncols'])
        nrows = int(header['nrows'])
        cellsize = header['cellsize']
        xll = header.get('xllcorner', 0.0)
        yll = header.get('yllcorner', 0.0)
        data = np.loadtxt(f)
    # ESRI ASCII stores rows from top to bottom; create coordinates accordingly
    xs = xll + np.arange(ncols) * cellsize
    ys = yll + np.arange(nrows) * cellsize
    return data, xs, ys, header


def plot_surface(z, xs, ys, out_path, elev=30, azim=45, zscale=1.0, title=None):
    X, Y = np.meshgrid(xs, ys)
    Z = z * zscale
    fig = plt.figure(figsize=(6, 5))
    ax = fig.add_subplot(111, projection='3d')
    surf = ax.plot_surface(X, Y, Z, rstride=1, cstride=1, cmap=cm.terrain,
                           linewidth=0, antialiased=True)
    ax.set_zlabel('Elevation (m)')
    ax.set_xlabel('X')
    ax.set_ylabel('Y')
    ax.view_init(elev=elev, azim=azim)
    if title:
        ax.set_title(title)
    fig.colorbar(surf, shrink=0.5, aspect=12)
    plt.tight_layout()
    plt.savefig(out_path, dpi=150)
    plt.close(fig)


def main():
    grid, xs, ys, header = read_esri_ascii(DTM_PATH)
    # read volume history (for example-driven scaling)
    vol = pd.read_csv(VOLUME_CSV)

    base = grid.copy()
    nsteps = len(vol)
    for i, row in vol.iterrows():
        # Simulate a small change to DTM per timestep.
        # Here: add a smooth radial "water level" or deformation proportional to volume.
        t = int(row['timestep'])
        # normalized factor around 1.0 (use min/max of volumes)
        vmin = vol['volume_m3'].min()
        vmax = vol['volume_m3'].max()
        if vmax > vmin:
            factor = 1.0 + (row['volume_m3'] - vmin) / (vmax - vmin) * 0.2 - 0.1
        else:
            factor = 1.0
        # create a gentle radial bump at center whose amplitude varies with factor
        nr, nc = base.shape
        cx = (nc - 1) / 2.0
        cy = (nr - 1) / 2.0
        bump = np.zeros_like(base, dtype=float)
        for r in range(nr):
            for c in range(nc):
                d = math.hypot(c - cx, r - cy)
                bump[r, c] = math.exp(-0.2 * d * d)
        # Apply bump scaled by factor and a small time-dependent phase for variation
        Z = base + (factor - 1.0) * 6.0 * bump + 0.3 * math.sin(i * 0.6) * bump

        out_file = os.path.join(FRAMES_DIR, f'frame_{t:02d}.png')
        title = f"Timestep {t}: {row['date']} — volume {row['volume_m3']} m3"
        # rotate azimuth a bit each frame for iterative visualization
        azim = 45 + i * (360.0 / nsteps)
        plot_surface(Z, xs, ys, out_file, elev=30, azim=azim, zscale=1.0, title=title)
        print('Saved', out_file)

    print('\nAll frames saved to', FRAMES_DIR)
    print('To build an animated GIF (optional), you can run:')
    print('  magick convert -delay 40 -loop 0 frames/frame_*.png animation.gif')


if __name__ == '__main__':
    main()
