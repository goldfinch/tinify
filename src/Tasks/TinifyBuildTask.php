<?php

namespace Goldfinch\Tinify\Tasks;

use Tinify\Source;
use Tinify\Tinify;
use SilverStripe\Assets\Image;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Assets\Storage\Sha1FileHashingService;
use SilverStripe\Core\Environment;

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
            $sha1FileHash = new Sha1FileHashingService;

            foreach($untinifiedImages as $image)
            {
                // only published images
                if ($image->canViewStage())
                {
                    $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
                    $basedir = dirname($reflection->getFileName(), 3);

                    $imageFile = $basedir . '/public' . $image->Link();
                    $source = Source::fromFile($imageFile);

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
            }
        }

        echo $imageCount ? 'Images tinified: ' . $imageCount : 'Nothing to compress';
    }
}
