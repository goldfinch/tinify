<?php

namespace Goldfinch\Tinify\Tasks;

use Tinify\Source;
use Tinify\Tinify;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Assets\Storage\Sha1FileHashingService;

// use function Tinify\fromUrl;

class TinifyBuildTask extends BuildTask
{
    private static $segment = 'Tinify';

    protected $enabled = true;

    protected $title = 'Tinify compressor';

    protected $description = 'Compress image assets';

    public function run($request)
    {
        $client = new Tinify();
        $client->setKey(Environment::getEnv('TINIFY_API_KEY'));

        $untinifiedImages = Image::get()->filter('Tinified', 0);

        $imageCount = 0;

        if ($untinifiedImages->Count())
        {
            // $sha1FileHash = new Sha1FileHashingService;

            foreach($untinifiedImages as $image)
            {
                // only published images
                if ($image->canViewStage())
                {
                    $this->s3($image);
                }
            }
        }

        echo $imageCount ? 'Images tinified: ' . $imageCount : 'Nothing to compress';
    }

    protected function local()
    {
        $basedir = Director::baseFolder();

        $imageFile = $basedir . '/public' . $image->Link();
        $source = Source::fromFile($imageFile);
        // $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
        // $basedir = dirname($reflection->getFileName(), 3);

        // $reflectionProperty = new \ReflectionProperty($source, 'url');
        // $reflectionProperty->setAccessible(true);
        // $sourceUrl = $reflectionProperty->getValue($source);

        // if ($sourceUrl)
        if ($source)
        {
            // if ($stream = fopen($sourceUrl, 'r'))
            if ($stream = fopen('data://text/plain;base64,' . base64_encode($source->toBuffer()), 'r'))
            {
                $source->toFile($imageFile);

                $image->FileHash = $sha1FileHash->computeFromStream($stream);
                $image->Tinified = 1;
                $image->write();
                $image->publishSingle();

                $imageCount++;

                fclose($stream);
            }
        }
    }

    protected function s3($media)
    {
        $source = Source::fromUrl($media->getUrl());

        $source->store([
            'service' => 's3',
            'aws_access_key_id' => Environment::getEnv('AWS_ACCESS_KEY_ID'),
            'aws_secret_access_key' => Environment::getEnv('AWS_SECRET_ACCESS_KEY'),
            'region' => Environment::getEnv('AWS_REGION'),
            'path' => Environment::getEnv('AWS_BUCKET_NAME') . '/' . $media->RelativeLink(),
        ]);

        if ($source) {

          // Conversions
          // foreach($media->media->registerMedias() as $k => $c)
          // {
          //     foreach($c as $k2 => $c2)
          //     {
          //         if ($media->hasGeneratedConversion($k2) && isset($c2[3]) && $c2[3]) {

          //           $source2 = fromUrl($media->getUrl($k2));
          //           $this->line('conversion ' . $k2);

          //           $source2->store([
          //               'service' => 's3',
          //               'aws_access_key_id' => env('AWS_ACCESS_KEY_ID'),
          //               'aws_secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
          //               'region' => env('AWS_DEFAULT_REGION'),
          //               'path' => env('AWS_BUCKET') . '/' . $media->getPath($k2),
          //           ]);
          //         }
          //     }
          // }

          $media->Tinified = 1;
          $media->write();
          // $image->publishSingle();

          // $this->line('media ' . $media->id . ' optimized');
        }

        // $source->toFile($media->getPath());
        // $media->setMeta('optimized', 1);
    }
}
