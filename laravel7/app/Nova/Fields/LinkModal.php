<?php

namespace App\Nova\Fields;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * Class LinkModal
 *
 * @package App\Nova\Fields
 */
class LinkModal extends Field
{
    /**
     * Prevent component to load via resource relationship
     *
     * @var null $viaResource
     */
    protected $viaRelationship = null;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'link-modal';

    /**
     * Indicates if the element should be shown on the index view.
     *
     * @var bool
     */
    public $showOnIndex = true;

    /**
     * Indicates if the element should be shown on the details view.
     *
     * @var bool
     */
    public $showOnDetail = true;

    /**
     * Indicates if the element should be shown on the creation view.
     *
     * @var bool
     */
    public $showOnCreation = false;

    /**
     * Indicates if the element should be shown on the update view.
     *
     * @var bool
     */
    public $showOnUpdate = false;

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param  NovaRequest  $request
     * @param  string  $requestAttribute
     * @param  object  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(
        NovaRequest $request,
        $requestAttribute,
        $model,
        $attribute
    ) {
        if ($request->exists($requestAttribute)) {
            $model->{$attribute} = $request[$requestAttribute];
        }
    }

    /**
     * Initialize which component to build
     *
     * @param string $title
     * @param bool $tab
     * @return LinkModal
     */
    public function build(string $title, bool $tab = false)
    {
        if (!empty($this->viaRelationship)) {
            return $this;
        }

        return $this->withMeta([
            'title' => $title,
            'tab' => $tab
        ]);
    }

    /**
     * Set component visibility
     *
     * @param $viaRelationship
     * @return LinkModal
     */
    public function isViaRelationship($viaRelationship = null)
    {
        $this->viaRelationship = $viaRelationship;
        return $this;
    }
}
