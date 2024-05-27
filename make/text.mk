##########
# Colors #
##########

EXAMPLE_COLOR_RESET   := \033[0m
EXAMPLE_COLOR_ERROR   := \033[31m
EXAMPLE_COLOR_INFO    := \033[32m
EXAMPLE_COLOR_WARNING := \033[33m
EXAMPLE_COLOR_COMMENT := \033[36m

######################
# Special Characters #
######################

# Usage:
#   $(call example_message, Foo$(,) bar) = Foo, bar
#   $(call example_message, $(lp)Foo bar) = (Foo bar
#   $(call example_message, Foo$(rp) bar) = Foo) bar

, := ,
lp := (
rp := )

########
# Time #
########

# Usage:
#   $(call example_time) = 11:06:20

define example_time
`date -u +%T`
endef

###########
# Message #
###########

# Usage:
#   $(call example_message, Foo bar)         = Foo bar
#   $(call example_message_success, Foo bar) = (っ◕‿◕)っ Foo bar
#   $(call example_message_warning, Foo bar) = ¯\_(ツ)_/¯ Foo bar
#   $(call example_message_error, Foo bar)   = (╯°□°)╯︵ ┻━┻ Foo bar

define example_message
	printf "$(EXAMPLE_COLOR_INFO)$(strip $(1))$(EXAMPLE_COLOR_RESET)\n"
endef

define example_message_success
	printf "$(EXAMPLE_COLOR_INFO)(っ◕‿◕)っ $(strip $(1))$(EXAMPLE_COLOR_RESET)\n"
endef

define example_message_warning
	printf "$(EXAMPLE_COLOR_WARNING)¯\_(ツ)_/¯ $(strip $(1))$(EXAMPLE_COLOR_RESET)\n"
endef

define example_message_error
	printf "$(EXAMPLE_COLOR_ERROR)(╯°□°)╯︵ ┻━┻ $(strip $(1))$(EXAMPLE_COLOR_RESET)\n"
endef

#######
# Log #
#######

# Usage:
#   $(call example_log, Foo bar)         = [11:06:20] [target] Foo bar
#   $(call example_log_warning, Foo bar) = [11:06:20] [target] ¯\_(ツ)_/¯ Foo bar
#   $(call example_log_error, Foo bar)   = [11:06:20] [target] (╯°□°)╯︵ ┻━┻ Foo bar

define example_log
	printf "[$(EXAMPLE_COLOR_COMMENT)$(call example_time)$(EXAMPLE_COLOR_RESET)] [$(EXAMPLE_COLOR_COMMENT)$(@)$(EXAMPLE_COLOR_RESET)] " ; $(call example_message, $(1))
endef

define example_log_warning
	printf "[$(EXAMPLE_COLOR_COMMENT)$(call example_time)$(EXAMPLE_COLOR_RESET)] [$(EXAMPLE_COLOR_COMMENT)$(@)$(EXAMPLE_COLOR_RESET)] "  ; $(call example_message_warning, $(1))
endef

define example_log_error
	printf "[$(EXAMPLE_COLOR_COMMENT)$(call example_time)$(EXAMPLE_COLOR_RESET)] [$(EXAMPLE_COLOR_COMMENT)$(@)$(EXAMPLE_COLOR_RESET)] " ;  $(call example_message_error, $(1))
endef

###########
# Confirm #
###########

# Usage:
#   $(call example_confirm, Foo bar) = ༼ つ ◕_◕ ༽つ Foo bar (y/N):
#   $(call example_confirm, Bar foo, y) = ༼ つ ◕_◕ ༽つ Foo bar (Y/n):

define example_confirm
	$(if $(CONFIRM),, \
		printf "$(EXAMPLE_COLOR_INFO) ༼ つ ◕_◕ ༽つ $(EXAMPLE_COLOR_WARNING)$(strip $(1)) $(EXAMPLE_COLOR_RESET)$(EXAMPLE_COLOR_WARNING)$(if $(filter y,$(2)),(Y/n),(y/N))$(EXAMPLE_COLOR_RESET): " ; \
		read CONFIRM ; \
		case $$CONFIRM in $(if $(filter y,$(2)), \
			[nN]$(rp) printf "\n" ; exit 1 ;; *$(rp) ;;, \
			[yY]$(rp) ;; *$(rp) printf "\n" ; exit 1 ;; \
		) esac \
	)
endef

################
# Conditionals #
################

# Usage:
#   $(call example_error_if_not, $(FOO), FOO has not been specified) = (╯°□°)╯︵ ┻━┻ FOO has not been specified

define example_error_if_not
	$(if $(strip $(1)),, \
		$(call example_message_error, $(strip $(2))) ; exit 1 \
	)
endef

# Usage:
#   $(call example_confirm_if, $(FOO), Foo bar) = ༼ つ ◕_◕ ༽つ Foo bar (y/N):

define example_confirm_if
	$(if $(strip $(1)), \
		$(call example_confirm, $(strip $(2)))
	)
endef

# Usage:
#   $(call example_confirm_if_not, $(FOO), Foo bar) = ༼ つ ◕_◕ ༽つ Foo bar (y/N):

define example_confirm_if_not
	$(if $(strip $(1)),, \
		$(call example_confirm, $(strip $(2)))
	)
endef

##########
# Random #
##########

# Usage:
#   $(call example_rand, 8) = 8th56zp2

define example_rand
`cat /dev/urandom | LC_ALL=C tr -dc 'a-z0-9' | fold -w $(strip $(1)) | head -n 1`
endef
