<?php

namespace App\Nova\Tools;

use App\Domain\NovaComponents\Factories\ComponentConfigFactory;
use Laravel\Nova\ResourceTool;

/**
 * Class ResourceTools
 *
 * @package App\Nova\Tools
 */
class ResourceTools extends ResourceTool
{
    /**
     * Prevent component to load via resource relationship
     *
     * @var null $viaResource
     */
    protected $viaRelationship = null;


    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Resource Tools';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'resource-tools';
    }

    /**
     * Initialize which component to build
     *
     * @param string $component
     * @param array $data
     * @return ResourceTools
     */
    public function build(string $component, array $data = [])
    {
        if (!empty($this->viaRelationship)) {
            return $this;
        }

        $config = ComponentConfigFactory::load($component, $data);
        return $this->withMeta(['config' => $config]);
    }

    /**
     * Set component visibility
     *
     * @param $viaRelationship
     * @return ResourceTool
     */
    public function isViaRelationship($viaRelationship = null)
    {
        $this->viaRelationship = $viaRelationship;
        return $this;
    }
}
