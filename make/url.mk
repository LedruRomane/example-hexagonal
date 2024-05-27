########
# Help #
########

EXAMPLE_HELP_PROJECT = $(EXAMPLE_COLOR_COMMENT)┏(°.°)┛┗(°.°)┓$(EXAMPLE_COLOR_RESET) ♪♫ Let's party ♫♪ $(EXAMPLE_COLOR_COMMENT)┗(°.°)┛┏(°.°)┓$(EXAMPLE_COLOR_RESET)\n
EXAMPLE_HELP_PROJECT += $(call example_help,Front,            http://127.0.0.1:63281)
EXAMPLE_HELP_PROJECT += $(call example_help,Back,             http://127.0.0.1:63280)
EXAMPLE_HELP_PROJECT += $(call example_help,Mailer,           http://127.0.0.1:62551)
EXAMPLE_HELP_PROJECT += $(call example_help,GraphiQL,         http://127.0.0.1:63280/graphiql)
EXAMPLE_HELP_PROJECT += $(call example_help,Symfony profiler, http://127.0.0.1:63280/_profiler)
