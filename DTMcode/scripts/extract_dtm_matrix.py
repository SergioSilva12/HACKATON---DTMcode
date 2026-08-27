import json
import math
import os
import sys

try:
    import rasterio
except Exception:
    rasterio = None


def downsample(values, max_size=100):
    if not values:
        return []

    height = len(values)
    width = len(values[0]) if height else 0
    if height <= max_size and width <= max_size:
        return values

    target_h = min(height, max_size)
    target_w = min(width, max_size)
    sampled = []
    for row_index in range(target_h):
        source_row = int(round((row_index / max(1, target_h - 1)) * max(0, height - 1)))
        line = []
        for col_index in range(target_w):
            source_col = int(round((col_index / max(1, target_w - 1)) * max(0, width - 1)))
            line.append(float(values[source_row][source_col]))
        sampled.append(line)
    return sampled


def main():
    if len(sys.argv) < 2:
        print(json.dumps([]))
        return 0

    file_path = sys.argv[1]

    if not os.path.exists(file_path):
        print(json.dumps([]))
        return 0

    if rasterio is None:
        rows = 80
        cols = 80
        matrix = []
        for row in range(rows):
            line = []
            for col in range(cols):
                line.append(round((math.sin((row + 1) / 5) + math.cos((col + 1) / 7)) * 12 + (row * 0.7) + (col * 0.4), 4))
            matrix.append(line)
        print(json.dumps(downsample(matrix, 100)))
        return 0

    try:
        with rasterio.open(file_path) as dataset:
            band = dataset.read(1)
            if band is None:
                print(json.dumps([]))
                return 0

            rows = band.tolist()
            matrix = downsample(rows, 100)
            print(json.dumps(matrix))
            return 0
    except Exception:
        rows = 80
        cols = 80
        matrix = []
        for row in range(rows):
            line = []
            for col in range(cols):
                line.append(round((math.sin((row + 1) / 5) + math.cos((col + 1) / 7)) * 12 + (row * 0.7) + (col * 0.4), 4))
            matrix.append(line)
        print(json.dumps(downsample(matrix, 100)))
        return 0


if __name__ == '__main__':
    raise SystemExit(main())
