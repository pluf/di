# Definitions

To let Pluf DI know what to inject and where, you have several options:

- use [autowiring](autowiring.md)
- use [annotations](annotations.md)
- use [PHP definitions](php-definitions.md)

You can also use several or all these options at the same time if you want to.

If you combine several sources, there are priorities that apply. From the highest priority to the least:

- Explicit definition on the container (i.e. defined with `$container->set()`)
- PHP file definitions (if you add several configuration files, then the last one can override entries from the previous ones)
- Annotations
- Autowiring

Read more in the [Definition overriding documentation](definition-overriding.md)

