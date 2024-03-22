COMMAND_NAME := novaxis
LOCAL_COMMAND_BIN_PATH := $(PWD)/bin/$(COMMAND_NAME)
COMMAND_BIN_PATH := /bin/$(COMMAND_NAME)

.PHONY: help install uninstall

help:
	@echo "Available commands:"
	@echo "  install    - Creates a symbolic link for the '$(COMMAND_NAME)' command in /bin."
	@echo "               This operation may require superuser permissions."
	@echo "  uninstall  - Removes the symbolic link for the '$(COMMAND_NAME)' command from /bin."
	@echo "               Use this to clean up after an install. May require superuser permissions."
	@echo "  help       - Displays this help message."

install:
	@if [ ! -f "$(COMMAND_BIN_PATH)" ]; then \
		if [ -f "$(LOCAL_COMMAND_BIN_PATH)" ]; then \
			echo "Creating symlink for $(COMMAND_NAME) in /bin"; \
			echo "Novaxis installed successfully."; \
		else \
			echo "Local command binary does not exist: $(LOCAL_COMMAND_BIN_PATH)"; \
		fi; \
	else \
		echo "The file already exists: $(COMMAND_BIN_PATH), no action taken."; \
	fi

uninstall:
	@if [ -L "$(COMMAND_BIN_PATH)" ]; then \
		echo "Removing symlink for $(COMMAND_NAME) from /bin"; \
		echo "Novaxis uninstalled successfully."; \
		rm $(COMMAND_BIN_PATH); \
	elif [ -f "$(COMMAND_BIN_PATH)" ]; then \
		echo "The path exists but is not a symlink: $(COMMAND_BIN_PATH). Manual removal required."; \
	else \
		echo "Symlink does not exist: $(COMMAND_BIN_PATH), no action taken."; \
	fi