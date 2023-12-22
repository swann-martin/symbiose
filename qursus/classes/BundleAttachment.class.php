<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace qursus;

use equal\orm\Model;

class BundleAttachment extends Model
{

    public static function getColumns()
    {
        return [
            'name' => [
                'type'              => 'string',
                'description'       => 'Name of the attachment file.',
                'multilang'         => true
            ],

            'url' => [
                'type'              => 'string',
                'description'       => 'Url of where is the file at on the server.',
                'multilang'         => true
            ],

            'bundle_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'qursus\Bundle',
                'description'       => 'Bundle the attachment relates to.',
                'ondelete'          => 'cascade'         // delete bundle when parent pack is deleted
            ]
        ];
    }
}
