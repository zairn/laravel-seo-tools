<?php

namespace SEO\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use SEO\Models\Page;
use SEO\Models\PageImage;
use SEO\Models\Setting;
use SEO\Contracts\LinkProvider;
use SEO\Tag;


class PageGeneratorJob //implements ShouldQueue
{
    // use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $linkProviders = config('seo.linkProviders', []);
        $total = 0;

        foreach ($linkProviders as $linkProvider) {

            $obj = new $linkProvider;

            if ($obj instanceof LinkProvider) {
                $links = $obj->all();
                foreach ($links as $link) {
                    $page = $this->page($link, $linkProvider);
                    if ($page->save()) {
                        $total++;
                        PageImage::where('page_id', $page->id)->delete();

                        if (isset($link['images']) && !empty($link['images']) && is_array($link['images'])) {
                            $this->pageImage($page, $link['images']);
                        }

                        if(isset($link['meta'])){
                            $page->saveMeta($link['meta'], []);
                            $tag = new Tag($page);
                            $tag->make()->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param Page $page
     * @param  array $images
     */
    protected function pageImage($page, $images)
    {
        foreach ($images as $image) {
            if (is_array($image)) {
                if (!isset($image['src'])) {
                    continue;
                }
                $pageImage = PageImage::create(['src' => $image['src'], 'page_id' => $page->id]);

                if (isset($image['title'])) {
                    $pageImage->title = $image['title'];
                }
                if (isset($image['caption'])) {
                    $pageImage->caption = $image['caption'];

                }
                if (isset($image['location'])) {
                    $pageImage->location = $image['location'];
                }

            } else {
                PageImage::create(['src' => $image, 'page_id' => $page->id]);
            }
        }
    }

    /**
     * @param $link
     * @return Page
     */
    protected function page($link, $linkProvider)
    {
        $setting = new Setting();

        $changeFrequency = $setting->getValueByKey('page_changefreq') ?? 'monthly';
        $priority = $setting->getValueByKey('page_priority') ?? 0.5;

        
        $object =  $linkProvider;
        $objectId = isset($link['id']) ? $link['id'] : null;
        
        $page = Page::firstOrNew(['object' => $object, 'object_id' => $objectId]);

        $page->object = $object;
        $page->object_id = $objectId;
        
        $page->path = $link['link'];
        $page->canonical_url = $link['link'];
        

        $page->title_source = isset($link['title']) ? substr($link['title'], 0, 70) : '';
        $page->title = isset($link['title']) ? substr($link['title'], 0, 70) : '';
        
        $page->description = isset($link['description']) ? substr($link['description'], 0, 150) : '';

        $page->created_at = isset($link['created_at']) ? $link['created_at'] : '';
        $page->updated_at = isset($link['updated_at']) ? $link['updated_at'] : '';
        
        $page->robot_index = isset($link['robot_index']) ? $link['robot_index'] : 'index';
        $page->robot_follow = isset($link['robot_follow']) ? $link['robot_follow'] : 'follow';
        
        $page->change_frequency = isset($link['change_frequency']) ? $link['change_frequency'] : $changeFrequency;
        $page->priority =  isset($link['priority']) ? $link['priority'] :  $priority;

        return $page;
    }
}
