# Pluf Dependecy Injection

The goal of the Pluf Dependency Injection (DI) is to free a business developer from the responsibility for obtaining objects that they need for its operation (which is called (separation of concerns)[https://en.wikipedia.org/wiki/Separation_of_concerns]).  Pluf DI is one of the most interesting parts of the framework. It is a compiled DI container, an important part of the platform which is used directly in routing and workflows.

## Installation

The recommended way to install is via Composer:

	composer require pluf/di

It requires PHP version 7.2 and higher.