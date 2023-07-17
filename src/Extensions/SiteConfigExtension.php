<?php

namespace Goldfinch\Tinify\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use LeKoala\Encrypt\EncryptHelper;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\ORM\ValidationResult;
use LeKoala\Encrypt\EncryptedDBVarchar;
use LeKoala\Encrypt\HasEncryptedFields;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class SiteConfigExtension extends DataExtension
{
    use HasEncryptedFields;

    private static $db = [
        'Tinify' => 'Boolean',
        'TinifyAPIKey' => EncryptedDBVarchar::class,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Configurations', [

            CompositeField::create(

                CheckboxField::create('Tinify', 'Tinify'),
                Wrapper::create(

                    TextField::create('TinifyAPIKey', 'API Key')->setDescription('refer to <a href="https://tinypng.com/developers" target="_blank">tinypng.com/developers</a>'),

                )->displayIf('Tinify')->isChecked()->end(),

            ),

        ]);

        // Set Encrypted Data
        $this->nestEncryptedData($fields);
    }

    public function validate(ValidationResult $validationResult)
    {
        // $validationResult->addError('Error message');
    }

    protected function nestEncryptedData(FieldList $fields)
    {
        foreach($this::$db as $name => $type)
        {
            if (EncryptHelper::isEncryptedField(get_class($this->owner), $name))
            {
                $this->owner->$name = $this->owner->dbObject($name)->getValue();
            }
        }
    }
}
