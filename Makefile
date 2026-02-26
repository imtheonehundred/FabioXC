# IPTV Panel — MAIN vs LB build (ARCHITECTURE.md §8)
#
# MAIN: full app (admin + streaming + modules). Default.
# LB:   streaming-only node (no admin UI, no reseller, minimal domain).
#
# Usage:
#   make install   # Composer + npm deps (MAIN)
#   make build    # Frontend build
#   make lb       # Create LB manifest (build/lb-manifest.txt) and build/lb/ stub

SHELL := /bin/bash
APP_ROOT ?= $(CURDIR)
BUILD_DIR := $(APP_ROOT)/build
LB_DIR := $(BUILD_DIR)/lb

.PHONY: install build lb lb-dirs clean

# Full install (MAIN)
install:
	composer install --no-dev --optimize-autoloader
	npm ci

# Frontend build
build:
	npm run build

# LB: create manifest and optional stub tree for streaming-only deploy
# LB node runs same codebase but only streaming entrypoints (public/stream.php, streaming routes)
# and crons that support streaming (streams, servers, cache). Admin routes not mounted.
lb: lb-dirs
	@echo "LB manifest and $(LB_DIR) created. For LB node: deploy full repo, point nginx to stream.php only, run schedule:run with env APP_CONTEXT=streaming (optional)."

lb-dirs: $(BUILD_DIR)
	@mkdir -p $(LB_DIR)
	@echo "# Paths needed on LB (streaming-only) node. Deploy these + Laravel base (bootstrap, config, storage, vendor, public)." > $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Streaming" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Stream" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Server" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Line" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Vod" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Bouquet" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Domain/Security" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "app/Infrastructure/Nginx" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "config/streaming.php" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "public/stream.php" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "routes/streaming.php" >> $(BUILD_DIR)/lb-manifest.txt
	@echo "Laravel: bootstrap, config, storage, vendor, public (all); exclude admin-only modules if desired." >> $(BUILD_DIR)/lb-manifest.txt

$(BUILD_DIR):
	mkdir -p $(BUILD_DIR)

clean:
	rm -rf $(BUILD_DIR)
