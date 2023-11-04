<?php

namespace Stillat\StatamicTemplateResolver;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Statamic\Facades\Antlers;

class StringTemplateManager
{
    /**
     * The supported template extensions.
     *
     * @var array|string[]
     */
    protected array $templateExtensions = [
        '.antlers.html',
        '.blade.php',
    ];

    /**
     * A mapping of already loaded templates.
     */
    protected array $templateCache = [];

    /**
     * The directory containing the templates.
     */
    protected string $templateDirectory = '';

    /**
     * Indicates if the default template exists.
     */
    protected bool $hasDefaultTemplate = false;

    /**
     * The details for the default template, if it exists.
     */
    protected array $defaultFileDetails = [];

    public function __construct(string $templateDirectory)
    {
        $this->templateDirectory = $templateDirectory;

        if (! Str::endsWith($this->templateDirectory, '/')) {
            $this->templateDirectory .= '/';
        }

        foreach ($this->templateExtensions as $extension) {
            $fileName = $this->templateDirectory.'default'.$extension;

            if (file_exists($fileName)) {
                $this->hasDefaultTemplate = true;

                $this->defaultFileDetails = [
                    'path' => $fileName,
                    'extension' => $extension,
                    'contents' => file_get_contents($fileName),
                ];
                break;
            }
        }
    }

    /**
     * Tests if a template exists.
     *
     * @param  string  $collectionHandle The collection handle.
     * @param  string  $blueprint The blueprint handle.
     */
    public function hasTemplate(string $collectionHandle, string $blueprint): bool
    {
        return $this->findBlueprintTemplate($collectionHandle, $blueprint) !== null;
    }

    /**
     * Renders a template for the provided collection and blueprint.
     *
     * @param  string  $collectionHandle The collection handle.
     * @param  array  $data The data to use when rendering the template.
     * @param  callable|null  $templateModifier An optional callback that can be used to modify the template contents.
     */
    public function render(string $collectionHandle, string $blueprintHandle, array $data = [], callable $templateModifier = null): ?string
    {
        $templateDetails = $this->findBlueprintTemplate($collectionHandle, $blueprintHandle);

        if (! $templateDetails) {
            return null;
        }

        $extension = $templateDetails['extension'];

        $contents = $templateDetails['contents'];

        if ($templateModifier !== null) {
            $contents = $templateModifier($contents, $data);
        }

        if ($extension === '.antlers.html') {
            return $this->renderAntlersTemplate($contents, $data);
        }

        if ($extension === '.blade.php') {
            return $this->renderBladeTemplate($contents, $data);
        }

        return null;
    }

    protected function renderAntlersTemplate(string $template, array $data = []): string
    {
        return (string) Antlers::parse($template, $data);
    }

    protected function renderBladeTemplate(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    protected function findBlueprintTemplate(string $collectionHandle, string $blueprintHandle): ?array
    {
        $cacheKey = 'blueprint:'.$collectionHandle.':'.$blueprintHandle;

        if (isset($this->templateCache[$cacheKey])) {
            return $this->templateCache[$cacheKey];
        }

        foreach ($this->templateExtensions as $extension) {
            $templatePath = $this->templateDirectory.$collectionHandle.'/'.$blueprintHandle.$extension;

            if (file_exists($templatePath)) {
                $templateDetails = [
                    'path' => $templatePath,
                    'extension' => $extension,
                    'contents' => file_get_contents($templatePath),
                ];

                $this->templateCache[$cacheKey] = $templateDetails;

                return $templateDetails;
            }
        }

        if (! $this->hasDefaultTemplate) {
            return null;
        }

        return $this->defaultFileDetails;
    }
}
