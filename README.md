# Attachment bundle

Eloquent extension for store simply file attachments.

It's under development, not recommended for production use!

# Installation

1. add bundle to composer: "hlacos/attachment": "dev-master"
2. composer install
3. add service provider to the providers list: 'Hlacos\Joboquent\AttachmentServiceProvider'
4. php artisan migrate --package="hlacos/attachment"
5. create directory: public/attachments
6. let it write by the web server

Attachments storing in public/attachments directory.
To override it:

1. php artisan config:publish hlacos/attachment
2. edit app/config/packages/hlacos/attachment.php

# Usage

<pre>
$attachment = new Attachment;
$attachment->addFile($filename);
$attachment->attachable()->associate($relatedModel);
$attachment->save();
</pre>

## Override table

Extend Hlacos\Attachment\Attachment and set the $table attribute.

## Set uploadable image required sizes

Extend Hlacos\Attachment\Attachment and set the $sizes array attribute.
In the array sets the width of the required images;

# Related models

You can set polymoprhic relations in the realted models.

<pre>
public function attachment() {
    return $this->morphOne('Hlacos\Attachment\Attachment', 'attachable');
}
</pre>

<pre>
public function attachment() {
    return $this->morphMany('Hlacos\Attachment\Attachment', 'attachable');
}
</pre>
