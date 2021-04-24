#!/usr/bin/make -f
IMAGE := laravel-app
VERSION := latest

.PHONY: all build rebuild shell run

# ------------------------------------------------------------------------------

all: build

# 建置 image
build:
	docker build -t=$(IMAGE):$(VERSION) .

# 不使用 cache 建置 image
rebuild:
	docker build -t=$(IMAGE):$(VERSION) --no-cache .

# 執行並使用 shell 進入 container
shell:
	docker run --rm -it -p 8000:8000 $(IMAGE):$(VERSION) bash

# 執行 container
run:
	docker run --rm -it -p 8000:8000 $(IMAGE):$(VERSION)
