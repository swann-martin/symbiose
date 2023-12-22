<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace qursus;

use equal\orm\Model;
use equal\orm\ObjectManager;

class Leaf extends Model
{

    public static function getColumns()
    {
        return [
            'identifier' => [
                'type'              => 'integer',
                'description'       => 'Unique identifier of the leaf within the page.',
                'onupdate'          => 'onupdateVisibility',
                'default'           => 1
            ],

            'order' => [
                'type'              => 'integer',
                'description'       => 'Position of the leaf in the page.',
                'default'           => 1
            ],

            'visible' => [
                'type'              => 'computed',
                'function'          => 'calcVisible',
                'description'       => 'JSON formatted array of visibility domain for leaf.',
                'result_type'       => 'string',
                'store'             => true,
            ],

            'visibility_rule' => [
                'type'              => 'string',
                'selection'         => [
                    'always visible'                    => 'always visible',
                    '$page.selection = $identifier'     => 'selection matches identifier',
                    '$page.submitted = true'            => 'page submitted',
                    '$page.submitted = false'           => 'page not submitted'
                ],
                'default'           => 'always visible',
                'onupdate'          => 'onupdateVisibility'
            ],

            'groups' => [
                'type'              => 'alias',
                'alias'             => 'groups_ids'
            ],

            'groups_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'qursus\Group',
                'foreign_field'     => 'leaf_id',
                'order'             => 'order',
                'sort'              => 'asc',
                'ondetach'          => 'delete'
            ],

            'background_image' => [
                'type'              => 'string',
                'description'       => "URL of the background image."
            ],

            'background_stretch' => [
                'type'              => 'boolean',
                'description'       => 'True to stretch the background image.',
                'default'           => false
            ],

            'background_opacity' => [
                'type'              => 'float',
                'description'       => "Opacity of the background (from 0 to 1).",
                'default'           => 0.5
            ],

            'contrast' => [
                'type'              => 'string',
                'selection'         => ['dark', 'light'],
                'default'           => 'light'
            ],

            'page_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'qursus\Page',
                'description'       => 'Page the leaf relates to.',
                'ondelete'          => 'cascade'         // delete leaf when parent page is deleted
            ]

        ];
    }

    /**
     * Generates a visible array based on the given object manager, ids, and language.
     *
     * @param ObjectManager $om The object manager instance.
     * @param array $oids An array of object ids.
     * @param string $lang The language to use for reading the leaves.
     * @return array The visible array.
     */
    public static function calcVisible(ObjectManager $om, array $oids, string $lang)
    {
        $result = [];

        // $leaves is an associative array mapping Model instances for each requested id
        $leaves = $om->read(__CLASS__, $oids, ['identifier', 'visibility_rule'], $lang);

        foreach ($leaves as $oid => $leaf) {
            if ($leaf['visibility_rule'] == 'always visible') {
                $result[$oid] = "[]";
            } else {
                // If the value is not numeric and not one of the values true or false, it is wrapped in single quotes
                $rule = str_replace('$identifier', $leaf['identifier'], $leaf['visibility_rule']);
                list($operand, $operator, $value) = explode(' ', $rule);
                // if the value is not numeric and not one of the values true or false, it is wrapped in single quotes
                if (!is_numeric($value) && !in_array($value, ['true', 'false'])) {
                    $value = "'$value'";
                }
                // the result calculated and then send in db is like ['$page.submitted','=',true]
                $result[$oid] = "['$operand','$operator',$value]";
            }
        }

        return $result;
    }

    public static function onupdateVisibility(ObjectManager $om, array $oids, array $values, string $lang): void
    {
        $om->update(__CLASS__, $oids, ['visible' => null], $lang);
    }
}
