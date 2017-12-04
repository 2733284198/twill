<?php

namespace A17\CmsToolkit\Repositories\Behaviors;

trait HandleSlugs
{
    public function afterSaveHandleSlugs($object, $fields)
    {
        if (property_exists($this->model, 'slugAttributes')) {
            foreach (getLocales() as $locale) {
                if ($object->getActiveSlug($locale) == null) {
                    $this->createOneSlug($object, $fields, $locale);
                } elseif (isset($fields['slug_' . $locale]) && !empty($fields['slug_' . $locale])) {
                    if (!isset($fields['active_' . $locale])) {
                        $object->disableLocaleSlugs($locale);
                    } else {
                        $currentSlug = [];
                        $currentSlug['slug'] = $fields['slug_' . $locale];
                        $currentSlug['locale'] = $locale;
                        $currentSlug = $this->getSlugParameters($object, $fields, $currentSlug);
                        $object->updateOrNewSlug($currentSlug);

                    }
                }
            }
        }
    }

    public function afterDeleteHandleSlugs($object)
    {
        $object->slugs()->delete();
    }

    public function afterRestoreHandleSlugs($object)
    {
        $object->slugs()->restore();
    }

    public function getFormFieldsHandleSlugs($object, $fields)
    {
        if ($object->slugs != null) {
            foreach ($object->slugs as $slug) {
                if ($slug->active) {
                    $fields['slug_' . $slug->locale] = $slug->slug;
                }
            }
        }

        return $fields;
    }

    private function createOneSlug($object, $fields, $locale)
    {
        $newSlug = [];

        if (isset($fields['slug_' . $locale]) && !empty($fields['slug_' . $locale])) {
            $newSlug['slug'] = $fields['slug_' . $locale];
        } elseif (isset($fields[reset($object->slugAttributes) . '_' . $locale]) && isset($fields['active_' . $locale])) {
            $newSlug['slug'] = $fields[reset($object->slugAttributes) . '_' . $locale];
        }

        if (!empty($newSlug)) {
            $newSlug['locale'] = $locale;
            $newSlug = $this->getSlugParameters($object, $fields, $newSlug);
            $object->updateOrNewSlug($newSlug);
        }
    }

    public function getSlugParameters($object, $fields, $slug)
    {
        $slugParams = $object->getSlugParams($slug['locale']);
        foreach ($object->slugAttributes as $param) {
            if (isset($slugParams[$param]) && isset($fields[$param])) {
                $slug[$param] = $fields[$param];
            } elseif (isset($slugParams[$param])) {
                $slug[$param] = $slugParams[$param];
            }
        }

        return $slug;
    }

    public function forSlug($slug, $with = [], $withCount = [], $scopes = [])
    {
        $item = $this->model->forSlug($slug)->with($with)->withCount($withCount)->where($scopes)->published()->visible()->first();

        if (!$item && $item = $this->model->forInactiveSlug($slug)->where($scopes)->published()->visible()->first()) {
            $item->redirect = true;
        }

        return $item;
    }

    public function forSlugPreview($slug, $with = [], $withCount = [])
    {
        return $this->model->forInactiveSlug($slug)->with($with)->withCount($withCount)->first();
    }

}
