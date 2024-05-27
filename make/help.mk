########
# Help #
########

.DEFAULT_GOAL := help

EXAMPLE_HELP = \
	Usage: make [$(EXAMPLE_COLOR_INFO)command$(EXAMPLE_COLOR_RESET)] \
	$(call example_help_section, Help) \
	$(call example_help,help,This help)

define example_help_section
	\n\n$(EXAMPLE_COLOR_COMMENT)$(strip $(1)):$(EXAMPLE_COLOR_RESET)
endef

define example_help
  \n  $(EXAMPLE_COLOR_INFO)$(1)$(EXAMPLE_COLOR_RESET) $(2)
endef

help:
	@printf "\n$(EXAMPLE_HELP)"
	@awk ' \
		BEGIN { \
			sectionsName[1] = "Commands" ; \
			sectionsCount = 1 ; \
		} \
		/^[-a-zA-Z0-9_.@%\/+]+:/ { \
			if (match(lastLine, /^## (.*)/)) { \
				command = substr($$1, 1, index($$1, ":") - 1) ; \
				section = substr(lastLine, RSTART + 3, index(lastLine, " - ") - 4) ; \
				if (section) { \
					message = substr(lastLine, index(lastLine, " - ") + 3, RLENGTH) ; \
					sectionIndex = 0 ; \
					for (i = 1; i <= sectionsCount; i++) { \
						if (sectionsName[i] == section) { \
							sectionIndex = i ; \
						} \
					} \
					if (!sectionIndex) { \
						sectionIndex = sectionsCount++ + 1 ; \
						sectionsName[sectionIndex] = section ; \
					} \
				} else { \
					message = substr(lastLine, RSTART + 3, RLENGTH) ; \
					sectionIndex = 1 ; \
				} \
				if (length(command) > sectionsCommandLength[sectionIndex]) { \
					sectionsCommandLength[sectionIndex] = length(command) ; \
				} \
				sectionCommandIndex = sectionsCommandCount[sectionIndex]++ + 1; \
				helpsCommand[sectionIndex, sectionCommandIndex] = command ; \
				helpsMessage[sectionIndex, sectionCommandIndex] = message ; \
			} \
		} \
		{ lastLine = $$0 } \
		END { \
			for (i = 1; i <= sectionsCount; i++) { \
				if (sectionsCommandCount[i]) { \
					printf "\n\n$(EXAMPLE_COLOR_COMMENT)%s:$(EXAMPLE_COLOR_RESET)", sectionsName[i] ; \
					for (j = 1; j <= sectionsCommandCount[i]; j++) { \
						printf "\n  $(EXAMPLE_COLOR_INFO)%-" sectionsCommandLength[i] "s$(EXAMPLE_COLOR_RESET) %s", helpsCommand[i, j], helpsMessage[i, j] ; \
					} \
				} \
			} \
		} \
	' $(MAKEFILE_LIST)
	@printf "\n\n"
	@printf "$(if $(EXAMPLE_HELP_PROJECT),$(EXAMPLE_HELP_PROJECT)\n\n)"
.PHONY: help

help.project:
	@printf "$(if $(EXAMPLE_HELP_PROJECT),\n$(EXAMPLE_HELP_PROJECT)\n\n)"
