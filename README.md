# debug-mcp-prompts

**⚠️ PROTOTYPE - FOR TESTING AND DISCUSSION PURPOSES ONLY**

**This repository contains only bootstrap files and documentation. No actual implementation code is included. This is a prototype to facilitate discussion and planning for the prompt templates.**

---

Code generation prompts for Symfony development, providing structured templates for creating console commands and other components.

## Purpose

Provides MCP prompts that guide LLMs in generating high-quality Symfony code:
- **Symfony Command Prompts**: Templates for creating console commands with best practices
- **Structured Generation**: Consistent code structure following Symfony conventions

## Features

- Template-based prompt generation
- Parameter substitution for customization
- Best practices guidance embedded in prompts
- Automatic discovery by debug-mcp server

## Installation

```bash
composer require wachterjohannes/debug-mcp-prompts
```

The prompts will be automatically discovered when debug-mcp server starts.

## Available Prompts

### symfony_command

Generate a Symfony Console Command with proper structure and best practices.

**Name**: `symfony_command`

**Parameters**:
- `command_name` (required): The command name (e.g., 'app:process-data')
- `description` (required): What the command does
- `interactive` (optional, default: false): Whether the command needs user interaction

**Example Usage**:

Via MCP protocol:
```json
{
  "jsonrpc": "2.0",
  "method": "prompts/get",
  "params": {
    "name": "symfony_command",
    "arguments": {
      "command_name": "app:import-users",
      "description": "Import users from CSV file",
      "interactive": true
    }
  },
  "id": 1
}
```

**Generated Prompt**:

The prompt will guide the LLM to create a command following this structure:
- Uses `#[AsCommand]` attribute
- Extends `Command` class
- Includes configure() method for arguments/options
- Implements execute() method with SymfonyStyle
- Adds dependency injection via constructor
- Includes error handling
- Interactive input handling (if requested)

## Template Structure

Prompts use template files in `templates/`:

```
templates/
├── command-basic.txt       # Non-interactive command template
└── command-interactive.txt # Interactive command template
```

Templates contain:
- Role markers (user/assistant)
- Parameter placeholders ({command_name}, {description})
- Step-by-step generation instructions
- Code structure guidance
- Best practices reminders

## Usage in Claude

When using with Claude Desktop configured with debug-mcp:

1. **List Available Prompts**:
   Ask Claude: "What prompts are available?"

2. **Use a Prompt**:
   "Use the symfony_command prompt to create a command named app:export-orders that exports orders to CSV"

3. **Customize Parameters**:
   Claude will use the prompt template with your provided parameters to generate the code.

## Registration

Prompts are registered via composer.json extra configuration:

```json
{
  "extra": {
    "wachterjohannes/debug-mcp": {
      "classes": [
        "Wachterjohannes\\DebugMcp\\Prompts\\SymfonyCommandPrompt"
      ]
    }
  }
}
```

## Adding New Prompts

To add a new prompt:

1. **Create Template File** in `templates/`:
   ```
   templates/entity-crud.txt
   ```

2. **Create Prompt Class**:
   ```php
   <?php
   namespace Wachterjohannes\DebugMcp\Prompts;

   use PhpMcp\Server\Attributes\McpPrompt;

   class EntityCrudPrompt
   {
       #[McpPrompt(
           name: 'symfony_entity_crud',
           description: 'Generate CRUD operations for a Symfony entity'
       )]
       public function generate(string $entityName): array
       {
           $template = file_get_contents(__DIR__ . '/../templates/entity-crud.txt');
           $content = str_replace('{entity_name}', $entityName, $template);

           return [
               ['role' => 'user', 'content' => $content]
           ];
       }
   }
   ```

3. **Register in composer.json**:
   ```json
   {
     "extra": {
       "wachterjohannes/debug-mcp": {
         "classes": [
           "Wachterjohannes\\DebugMcp\\Prompts\\EntityCrudPrompt"
         ]
       }
     }
   }
   ```

4. **Document in README**

## Template Guidelines

When creating prompt templates:

1. **Clear Instructions**: Provide step-by-step guidance
2. **Parameter Placeholders**: Use {param_name} for substitution
3. **Best Practices**: Embed Symfony best practices in instructions
4. **Code Examples**: Show expected output structure
5. **Error Handling**: Include error handling guidance
6. **Testing Hints**: Mention how to test the generated code

Example template structure:
```
Create a Symfony Console Command with the following specifications:

Command Name: {command_name}
Description: {description}

Requirements:
1. Use #[AsCommand] attribute
2. Extend Symfony\Component\Console\Command\Command
3. Use SymfonyStyle for output
4. Include proper error handling
5. Return appropriate exit codes (SUCCESS/FAILURE)

Generate the complete command class with:
- Namespace: App\Command
- Class name: Derived from command name (PascalCase + "Command" suffix)
- Constructor with dependency injection
- configure() method if arguments/options needed
- execute() method with full implementation
```

## Development

### Code Quality

Format code before committing:

```bash
composer cs-fix
```

### Testing Prompts

Test prompts by:
1. Installing package in debug-mcp instance
2. Configuring Claude Desktop
3. Using the prompt via natural language
4. Verifying generated code quality

## Requirements

- PHP 8.1 or higher
- modelcontextprotocol/php-sdk
- wachterjohannes/debug-mcp (for testing)

## Repository

GitHub: https://github.com/wachterjohannes/debug-mcp-prompts

## License

MIT
