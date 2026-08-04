from __future__ import annotations

import json
from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
PUBLIC_IMAGE_DIR = ROOT / "docs" / "img"
SOURCE_IMAGE_DIR = ROOT / "source-assets" / "images"
REPORT_PATH = ROOT / "tools" / "image-optimization-report.json"


def output(name: str, *, width: int | None = None, quality: int = 84) -> dict[str, object]:
    return {"name": name, "width": width, "quality": quality}


CONVERSIONS: list[dict[str, object]] = [
    {
        "source": "MainVisual_pc.png",
        "published_source": True,
        "outputs": [output("MainVisual_pc.webp", quality=84)],
    },
    *[
        {"source": f"mv-{page}_{size}.png", "outputs": [output(f"mv-{page}_{size}.webp", quality=86)]}
        for page in ("AboutUs", "Contact", "Facilities", "PriceList")
        for size in ("PC", "SP")
    ],
    {"source": "mv-FAQ_PC.png", "outputs": [output("mv-FAQ_PC.webp", quality=86)]},
    {"source": "mv-faq_SP.png", "outputs": [output("mv-faq_SP.webp", quality=86)]},
    *[
        {"source": f"reason_{number:02}.jpg", "outputs": [output(f"reason_{number:02}.webp", quality=84)]}
        for number in range(1, 4)
    ],
    {
        "source": "service_01.jpg",
        "crop_ratio": 3,
        "crop_y": 0.28,
        "outputs": [
            output("service_01-640.webp", width=640, quality=84),
            output("service_01-1280.webp", width=1280, quality=84),
            output("service_01.webp", width=1920, quality=84),
        ],
    },
    *[
        {
            "source": f"service_{number:02}.jpg",
            "crop_ratio": 3,
            "outputs": [output(f"service_{number:02}.webp", quality=84)],
        }
        for number in (2, 3)
    ],
    {
        "source": "dayservice_01.jpeg",
        "outputs": [
            output("dayservice_01-800.webp", width=800, quality=84),
            output("dayservice_01.webp", width=1600, quality=84),
        ],
    },
    *[
        {"source": f"dayservice_{number:02}.jpg", "outputs": [output(f"dayservice_{number:02}.webp", quality=84)]}
        for number in (2, 3)
    ],
    {"source": "stuffs.jpg", "outputs": [output("stuffs.webp", quality=84)]},
    *[
        {"source": f"Button{number:03}.png", "outputs": [output(f"Button{number:03}.webp", quality=88)]}
        for number in range(1, 6)
    ],
    *[
        {
            "source": f"anchorLink__faq{number:02}-PC.png",
            "outputs": [output(f"anchorLink__faq{number:02}-PC.webp", quality=88)],
        }
        for number in range(1, 5)
    ],
    {
        "source": "AnchorLink_PriceList-DayService.png",
        "outputs": [output("AnchorLink_PriceList-DayService.webp", quality=88)],
    },
    {
        "source": "AnchorLink_PriceList-HomeCare.png",
        "outputs": [output("AnchorLink_PriceList-HomeCare.webp", quality=88)],
    },
    *[
        {"source": f"sidebar{number:02}.png", "outputs": [output(f"sidebar{number:02}.webp", quality=88)]}
        for number in range(1, 3)
    ],
]


def crop_to_ratio(image: Image.Image, ratio: float, crop_y: float) -> Image.Image:
    target_height = round(image.width / ratio)
    if target_height >= image.height:
        return image.copy()

    top = round((image.height - target_height) * crop_y)
    return image.crop((0, top, image.width, top + target_height))


def resize_to_width(image: Image.Image, width: int | None) -> Image.Image:
    if width is None or width >= image.width:
        return image.copy()

    height = round(image.height * width / image.width)
    return image.resize((width, height), Image.Resampling.LANCZOS)


def save_webp(image: Image.Image, destination: Path, quality: int) -> None:
    converted = image if image.mode in {"RGB", "RGBA"} else image.convert("RGB")
    converted.save(destination, "WEBP", quality=quality, method=6)


def main() -> None:
    report: list[dict[str, object]] = []

    for conversion in CONVERSIONS:
        source_directory = (
            PUBLIC_IMAGE_DIR if conversion.get("published_source") else SOURCE_IMAGE_DIR
        )
        source_path = source_directory / str(conversion["source"])
        source_bytes = source_path.stat().st_size

        with Image.open(source_path) as source_image:
            source_image.load()
            working_image = source_image

            if conversion.get("crop_ratio"):
                working_image = crop_to_ratio(
                    source_image,
                    float(conversion["crop_ratio"]),
                    float(conversion.get("crop_y", 0.5)),
                )

            outputs: list[dict[str, object]] = []
            for output_spec in conversion["outputs"]:
                destination = PUBLIC_IMAGE_DIR / str(output_spec["name"])
                resized = resize_to_width(working_image, output_spec.get("width"))
                save_webp(resized, destination, int(output_spec["quality"]))
                outputs.append(
                    {
                        "name": destination.name,
                        "format": "WEBP",
                        "width": resized.width,
                        "height": resized.height,
                        "bytes": destination.stat().st_size,
                        "quality": output_spec["quality"],
                    }
                )

            report.append(
                {
                    "source": {
                        "name": source_path.name,
                        "format": source_image.format,
                        "width": source_image.width,
                        "height": source_image.height,
                        "bytes": source_bytes,
                    },
                    "outputs": outputs,
                }
            )

    REPORT_PATH.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


if __name__ == "__main__":
    main()
