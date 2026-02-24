<?php

namespace Core;

class View
{
    private string $templateDir;

    public function __construct(?string $templateDir = null)
    {
        $this->templateDir = $templateDir ?? __DIR__ . '/../templates/';
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->resolvePath($template);

        if (!file_exists($file)) {
            throw new \RuntimeException("Template not found: {$template}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;
        return ob_get_clean();
    }

    public function layout(string $layoutName, string $template, array $data = []): string
    {
        $data['content'] = $this->render($template, $data);
        return $this->render("layouts/{$layoutName}", $data);
    }

    public function component(string $name, array $data = []): string
    {
        return $this->render("components/{$name}", $data);
    }

    public static function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    private function resolvePath(string $template): string
    {
        $template = str_replace('.', '/', $template);

        if (!str_ends_with($template, '.php')) {
            $template .= '.php';
        }

        return $this->templateDir . $template;
    }
}
