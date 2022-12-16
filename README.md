# Instagram feed for octobercms.

After install go to backend/settings and find instagram.
Add the access token and the number of images to be display.

#Composer instalation
`composer require ideaseven/instagram-plugin`

## Available properties:
Property | Inspector Name | Description
-------- | -------------- | -----------
`getInstagramFeed()`| Function| Get all images from instagramm


## Page variables
Variable | Type | Description
-------- | ---- | -----------
`permalink` | `string` | String of `image url` rendering from instagram
`media_url`	 | `String` | String of `image path` rendering in front-end
`media_type`	 | `String` | String of `image type` for checking the type of rendering
`caption`	 | `String` | String of `description` rendering in front-end


## Example of custom markup

```html
{% set instagramFeed = getInstagramFeed() %}
{% for row in instagramFeed %}
    {% if row.media_type == 'IMAGE' %}
    <div class="item wow fadeInUp" data-wow-duration="1s" data-wow-delay=".3s">
        <div class="image">
            <a href="{{ row.permalink }}" target="_blank">
                <img src="{{ row.media_url }}" alt="service-1">
            </a>
        </div>
        <div class="header-text p-1">
            <a href="{{ row.permalink }}" target="_blank">
                <p class="mb-0"><small class="text">{{ html_limit(row.caption, 55) }}{{ row.caption|length > 55 ? '...' }}</small></p>
            </a>
        </div>
    </div>
    {% endif %}
{% endfor %}
```
