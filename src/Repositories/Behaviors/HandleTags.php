<?php

namespace A17\CmsToolkit\Repositories\Behaviors;

trait HandleTags
{

    public function afterSaveHandleTags($object, $fields)
    {
        if (!isset($fields['bulk_tags']) && !isset($fields['previous_common_tags'])) {
            $object->setTags($fields['tags'] ?? []);
        } else {
            $previousCommonTags = $fields['previous_common_tags']->pluck('name')->toArray();

            if (!empty($previousCommonTags)) {
                if (!empty($difference = array_diff($previousCommonTags, $fields['bulk_tags'] ?? []))) {
                    $object->untag($difference);
                }
            }

            $object->tag($fields['bulk_tags'] ?? []);
        }
    }

    protected function filterHandleTags($query, &$scopes)
    {
        $this->addRelationFilterScope($query, $scopes, 'tag_id', 'tags');
    }

    private function getTagsQuery()
    {
        return $this->model->allTags()->orderBy('count', 'desc');
    }

    public function getTags($query = '', $ids = [])
    {
        $tagQuery = $this->getTagsQuery();

        if (!empty($query)) {
            $tagQuery->where('slug', 'like', '%' . $query . '%');
        }

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $tagQuery->whereHas('tagged', function ($query) use ($id) {
                    $query->where('taggable_id', $id);
                });
            }
        }

        return $tagQuery->get();
    }

    public function getTagsList()
    {
        return $this->getTagsQuery()->where('count', '>', 0)->pluck('name', 'id')->toArray();
    }

}
