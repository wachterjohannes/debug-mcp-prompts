# debug-mcp-prompts - Prompt Provider Package

## Project Overview

This package provides code generation prompts for Symfony development, enabling LLMs to generate high-quality, convention-compliant code through structured templates.

**Role in Ecosystem**: Extension package providing code generation guidance

**Key Responsibility**: Structured prompt templates for Symfony component generation

## Architecture

### Component Structure

```
debug-mcp-prompts
└── SymfonyCommandPrompt
    ├── Basic template (command-basic.txt)
    └── Interactive template (command-interactive.txt)
```

### Prompt Pattern

Prompts return message arrays for LLM conversation:

```php
use PhpMcp\Server\Attributes\McpPrompt;

class PromptProvider
{
    #[McpPrompt(
        name: 'prompt_name',
        description: 'What this prompt generates'
    )]
    public function generate(string $param): array
    {
        // Load template
        $template = file_get_contents(__DIR__ . '/../templates/template.txt');

        // Substitute parameters
        $content = str_replace('{param}', $param, $template);

        // Return message array
        return [
            ['role' => 'user', 'content' => $content]
        ];
    }
}
```

## Prompt Specification

### SymfonyCommandPrompt

**Purpose**: Guide LLM in generating Symfony Console Commands

**Name**: `symfony_command`

**Parameters**:
- `command_name` (string, required): Command identifier (e.g., 'app:process-data')
- `description` (string, required): Command purpose description
- `interactive` (bool, optional, default: false): Whether command requires user interaction

**Implementation Notes**:
- Loads template based on `interactive` parameter
- Substitutes {command_name} and {description} placeholders
- Returns structured prompt guiding LLM through command creation

**Template Selection Logic**:
```php
if ($interactive) {
    $template = 'command-interactive.txt';
} else {
    $template = 'command-basic.txt';
}
```

**Return Format**:
```php
return [
    [
        'role' => 'user',
        'content' => $promptText
    ]
];
```

## Template Design

### Template Structure

Templates are plain text files with:
- **Instructions**: Step-by-step generation guidance
- **Placeholders**: `{param_name}` for parameter substitution
- **Examples**: Code structure examples
- **Best Practices**: Embedded Symfony conventions
- **Requirements**: Mandatory elements to include

### command-basic.txt

Non-interactive command template covering:
- Command class structure
- #[AsCommand] attribute usage
- Basic execute() implementation
- SymfonyStyle for output
- Dependency injection pattern
- Error handling
- Exit code usage

### command-interactive.txt

Interactive command template including:
- All basic command elements
- Question helper usage
- ConfirmationQuestion examples
- ChoiceQuestion examples
- Input validation
- interact() method implementation

## Development Guidelines

### Creating Templates

**Template Naming**: Lowercase with hyphens (e.g., `entity-crud.txt`)

**Placeholder Format**: Use curly braces `{parameter_name}`

**Content Guidelines**:
1. **Start with Context**: Explain what will be generated
2. **List Requirements**: Mandatory elements and conventions
3. **Provide Structure**: Expected code organization
4. **Include Examples**: Show code snippets
5. **Embed Best Practices**: Symfony standards and patterns
6. **Error Handling**: Guide on exception handling
7. **Testing Hints**: How to verify generated code

**Example Template**:
```
Create a Symfony Console Command with these specifications:

Command Name: {command_name}
Description: {description}

Requirements:
1. Use #[AsCommand] attribute for command definition
2. Extend Symfony\Component\Console\Command\Command
3. Implement execute() method returning int (SUCCESS or FAILURE)
4. Use SymfonyStyle for formatted output
5. Include proper error handling with try-catch
6. Add PHPDoc comments for class and methods

Structure:
- Namespace: App\Command
- Class name: Convert command name to PascalCase + "Command" suffix
  Example: app:process-data → ProcessDataCommand
- Constructor: Use dependency injection for services
- configure(): Define arguments and options if needed
- execute(): Main command logic

Generate the complete command class following these requirements.
```

### Adding New Prompts

**Step 1**: Create template file
```bash
touch templates/new-prompt.txt
```

**Step 2**: Create prompt class
```php
<?php
namespace Wachterjohannes\DebugMcp\Prompts;

use PhpMcp\Server\Attributes\McpPrompt;

class NewPrompt
{
    #[McpPrompt(
        name: 'symfony_new_feature',
        description: 'Generate a new Symfony feature'
    )]
    public function generate(
        string $featureName,
        bool $includingTests = false
    ): array {
        $templateFile = $includingTests ? 'new-prompt-with-tests.txt' : 'new-prompt.txt';
        $template = file_get_contents(__DIR__ . "/../templates/{$templateFile}");

        $content = str_replace(
            ['{feature_name}', '{include_tests}'],
            [$featureName, $includingTests ? 'yes' : 'no'],
            $template
        );

        return [
            ['role' => 'user', 'content' => $content]
        ];
    }
}
```

**Step 3**: Register in composer.json
```json
{
  "extra": {
    "wachterjohannes/debug-mcp": {
      "classes": [
        "Wachterjohannes\\DebugMcp\\Prompts\\NewPrompt"
      ]
    }
  }
}
```

### Code Style

- **PSR-12**: Follow PSR-12 coding standards
- **Type Hints**: All parameters and returns typed
- **File Handling**: Use absolute paths, check file existence
- **Parameter Validation**: Validate before using in templates
- **Error Handling**: Throw exceptions for invalid parameters

## Integration Points

### With debug-mcp Server

The server discovers prompts through:
1. Reading `vendor/composer/installed.json`
2. Finding this package's `extra.wachterjohannes/debug-mcp.classes`
3. Instantiating prompt classes
4. SDK discovers methods via `#[McpPrompt]` attributes

### With MCP SDK

Uses official `modelcontextprotocol/php-sdk` attributes:

```php
use PhpMcp\Server\Attributes\McpPrompt;

#[McpPrompt(
    name: 'prompt_identifier',
    description: 'Human-readable prompt purpose'
)]
```

**Return Format**:
Prompts must return arrays of message objects:
```php
[
    ['role' => 'user', 'content' => 'Prompt text'],
    ['role' => 'assistant', 'content' => 'Optional pre-filled response'],
]
```

## Key Implementation Patterns

### Template Loading

```php
private function loadTemplate(string $filename): string
{
    $path = __DIR__ . "/../templates/{$filename}";

    if (!file_exists($path)) {
        throw new \RuntimeException("Template not found: {$filename}");
    }

    return file_get_contents($path);
}
```

### Parameter Substitution

```php
private function substituteParameters(string $template, array $params): string
{
    $placeholders = array_map(
        fn($key) => "{{$key}}",
        array_keys($params)
    );

    return str_replace($placeholders, array_values($params), $template);
}
```

### Multi-Template Selection

```php
public function generate(string $type, bool $includeTests): array
{
    $templateName = match ($type) {
        'entity' => 'entity.txt',
        'controller' => 'controller.txt',
        'service' => 'service.txt',
        default => throw new \InvalidArgumentException("Unknown type: {$type}"),
    };

    if ($includeTests) {
        $templateName = str_replace('.txt', '-with-tests.txt', $templateName);
    }

    $content = $this->loadTemplate($templateName);
    return [['role' => 'user', 'content' => $content]];
}
```

## Sample Templates

### templates/command-basic.txt

```
Create a Symfony Console Command with the following specifications:

Command Name: {command_name}
Description: {description}

Generate a complete Symfony Console Command class following these requirements:

1. CLASS STRUCTURE:
   - Namespace: App\Command
   - Class name: Convert command name to PascalCase + "Command" suffix
     Example: app:send-emails → SendEmailsCommand
   - Use #[AsCommand] attribute with name and description
   - Extend Symfony\Component\Console\Command\Command

2. DEPENDENCIES:
   - Use constructor injection for required services
   - Type-hint all dependencies

3. IMPLEMENTATION:
   - Implement execute() method
   - Use SymfonyStyle for output formatting
   - Return Command::SUCCESS on success
   - Return Command::FAILURE on failure
   - Include try-catch for error handling

4. BEST PRACTICES:
   - Add PHPDoc comments
   - Use strict types declaration
   - Follow PSR-12 coding standards
   - Use SymfonyStyle methods: success(), error(), warning(), info()

Generate the complete, production-ready command class.
```

## SDK Attribute Reference

### #[McpPrompt]

**Required Properties**:
- `name` (string): Unique prompt identifier
- `description` (string): Human-readable prompt purpose

**Optional Properties**:
- Check SDK documentation for additional options

**Usage**:
```php
#[McpPrompt(
    name: 'my_prompt',
    description: 'Generates specific code pattern'
)]
```

## Quick Implementation Checklist

- [ ] `src/SymfonyCommandPrompt.php` - Command prompt class
- [ ] `templates/command-basic.txt` - Basic command template
- [ ] `templates/command-interactive.txt` - Interactive command template
- [ ] `composer.json` - Package definition with extra config
- [ ] `README.md` - User documentation
- [ ] `.php-cs-fixer.php` - Code style configuration
- [ ] Test prompt usage in Claude Desktop

## Future Prompt Ideas

Potential prompts to add:

1. **symfony_entity**: Generate Doctrine entity with repository
2. **symfony_controller**: Generate controller with routes
3. **symfony_form**: Generate form type with validation
4. **symfony_service**: Generate service with interface
5. **symfony_event_subscriber**: Generate event subscriber
6. **symfony_test**: Generate PHPUnit test class
7. **symfony_migration**: Generate database migration

## Repository Information

- **GitHub**: https://github.com/wachterjohannes/debug-mcp-prompts
- **Packagist**: (publish after implementation)
- **License**: MIT
