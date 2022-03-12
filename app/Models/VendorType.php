<?php

namespace App\Models;

class VendorType extends BaseModel
{


    protected $fillable = ['name', 'description', 'slug', 'is_active', 'color'];
    protected $appends = ['formatted_date', 'logo', 'website_header'];
    protected $casts = [
        'id' => 'int',
        'is_active' => 'int',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('logo')
            ->useFallbackUrl('' . url('') . '/images/default.png')
            ->useFallbackPath(public_path('/images/default.png'));
        $this
            ->addMediaCollection('feature_image')
            ->useFallbackUrl('' . url('') . '/images/default.png')
            ->useFallbackPath(public_path('/images/default.png'));
    }


    public function getLogoAttribute()
    {
        return $this->getFirstMediaUrl('logo');
    }

    public function getWebsiteHeaderAttribute()
    {
        return $this->getFirstMediaUrl('website_header');
    }


    public function getIsParcelAttribute()
    {
        return $this->slug == "parcel";
    }

    public function getIsServiceAttribute()
    {
        return $this->slug == "service";
    }

    public function scopeAssignable($query)
    {
        return $query->where('slug', '!=', "taxi");
    }
}
