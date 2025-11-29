<?php

declare(strict_types=1);

namespace Wachterjohannes\DebugMcp\Prompts;

use Mcp\Capability\Attribute\McpPrompt;

/**
 * Generates Symfony Console Command prompts for LLM code generation.
 *
 * Provides template-based prompts that guide LLMs in creating
 * production-ready Symfony Console Commands following best practices.
 */
class SymfonyCommandPrompt
{
    /**
     * Generates a prompt for creating a Symfony Console Command.
     *
     * @param string $commandName The command name (e.g., 'app:process-data')
     * @param string $description Description of what the command does
     * @param bool $interactive Whether the command requires user interaction
     *
     * @return array<int, array{role: string, content: string}> Message array for LLM conversation
     *
     * @throws \RuntimeException If template file cannot be loaded
     */
    #[McpPrompt(
        name: 'symfony_command',
        description: 'Generate a Symfony Console Command with proper structure and best practices'
    )]
    public function generate(
        string $commandName,
        string $description,
        bool $interactive = false
    ): array {
        // Validate parameters
        if (empty($commandName)) {
            throw new \InvalidArgumentException('Command name cannot be empty');
        }

        if (empty($description)) {
            throw new \InvalidArgumentException('Description cannot be empty');
        }

        // Select template based on interactive flag
        $templateFile = $interactive ? 'command-interactive.txt' : 'command-basic.txt';
        $template = $this->loadTemplate($templateFile);

        // Substitute parameters
        $content = $this->substituteParameters($template, [
            'command_name' => $commandName,
            'description' => $description,
        ]);

        // Return message array for LLM
        return [
            ['role' => 'user', 'content' => $content]
        ];
    }

    /**
     * Loads a template file from the templates directory.
     *
     * @param string $filename Template filename
     *
     * @return string Template content
     *
     * @throws \RuntimeException If template file does not exist or cannot be read
     */
    private function loadTemplate(string $filename): string
    {
        $path = __DIR__ . '/../templates/' . $filename;

        if (!file_exists($path)) {
            throw new \RuntimeException("Template not found: {$filename}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new \RuntimeException("Failed to read template: {$filename}");
        }

        return $content;
    }

    /**
     * Substitutes placeholders in template with actual parameter values.
     *
     * @param string $template Template content with {placeholder} markers
     * @param array<string, string> $params Parameter values to substitute
     *
     * @return string Template with parameters substituted
     */
    private function substituteParameters(string $template, array $params): string
    {
        $placeholders = array_map(
            fn($key) => '{' . $key . '}',
            array_keys($params)
        );

        return str_replace($placeholders, array_values($params), $template);
    }
}
