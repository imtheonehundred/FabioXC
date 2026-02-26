# Build MAIN (full) and LB (streaming-only) from the same codebase (ARCHITECTURE.md §8).
# Usage: make new   -> prepare full build
#        make lb    -> prepare LB build (excludes admin UI, reseller, optional modules)

MAIN_DIR ?= .
TEMP_DIR ?= /tmp/iptv_build
LB_DIR   ?= $(TEMP_DIR)/lb

# Directories to include in MAIN build
MAIN_FILES = app bootstrap config database public resources routes storage/lang lang infrastructure

# For LB build: same as MAIN but we remove admin-only parts after copy
LB_FILES = app bootstrap config database public resources routes storage/lang lang infrastructure

# Directories/files to remove for LB (streaming-only node)
LB_DIRS_TO_REMOVE = \
	app/Http/Controllers/Admin \
	app/Http/Controllers/Reseller \
	app/Http/Middleware/HandleInertiaRequests.php \
	resources/js/Pages/Admin \
	resources/js/Pages/Reseller \
	resources/js/Layouts/AdminLayout.vue \
	resources/js/Layouts/ResellerLayout.vue

.PHONY: new lb clean

new:
	@echo "==> MAIN build: $(MAIN_DIR)"
	@mkdir -p $(TEMP_DIR)/main
	@for d in $(MAIN_FILES); do \
		if [ -d "$(MAIN_DIR)/$$d" ]; then cp -R "$(MAIN_DIR)/$$d" "$(TEMP_DIR)/main/"; fi; \
		if [ -f "$(MAIN_DIR)/$$d" ]; then cp "$(MAIN_DIR)/$$d" "$(TEMP_DIR)/main/"; fi; \
	done
	@echo "==> MAIN build ready in $(TEMP_DIR)/main"

lb: new
	@echo "==> LB build: stripping admin/reseller"
	@rm -rf $(LB_DIR)
	@cp -R $(TEMP_DIR)/main $(LB_DIR)
	@for d in $(LB_DIRS_TO_REMOVE); do \
		rm -rf "$(LB_DIR)/$$d"; \
	done
	@echo "==> LB build ready in $(LB_DIR)"

clean:
	@rm -rf $(TEMP_DIR)
	@echo "==> Cleaned $(TEMP_DIR)"
