<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace qursus;

use equal\orm\Model;

class Page extends Model
{

    public static function getColumns()
    {
        return [
            'identifier' => [
                'type'              => 'integer',
                'description'       => 'Unique identifier of the page within the chapter.',
                'default'           => 1
            ],

            'order' => [
                'type'              => 'integer',
                'description'       => 'Position of the page in the chapter.',
                'default'           => 1
            ],

            'next_active' => [
                'type'              => 'computed',
                'description'       => "JSON formatted array of visibility domain for 'next' button.",
                'function'          => 'calcNextActive',
                'result_type'       => 'string',
                'store'             => true
            ],

            'next_active_rule' => [
                'type'              => 'string',
                'selection'         => [
                    'always visible'            => 'always visible',
                    '$page.submitted = true'    => 'page submitted',
                    '$page.selection > 0'       => 'item selected',
                    '$page.actions_counter > 0' => '1 or more actions',
                    '$page.actions_counter > 1' => '2 or more actions',
                    '$page.actions_counter > 2' => '3 or more actions',
                    '$page.actions_counter > 3' => '4 or more actions',
                    '$page.actions_counter > 4' => '5 or more actions',
                    '$page.actions_counter > 5' => '6 or more actions',
                    '$page.actions_counter > 6' => '7 or more actions'
                ],
                'default'           => 'always visible',
                'onupdate'          => 'onupdateNextActive'
            ],

            'leaves' => [
                'type'              => 'alias',
                'alias'             => 'leaves_ids'
            ],

            'leaves_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'qursus\Leaf',
                'foreign_field'     => 'page_id',
                'order'             => 'order',
                'sort'              => 'asc',
                'ondetach'          => 'delete'
            ],

            'sections' => [
                'type'              => 'alias',
                'alias'             => 'sections_ids'
            ],

            'sections_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'qursus\Section',
                'foreign_field'     => 'page_id',
                'order'             => 'order',
                'sort'              => 'asc',
                'ondetach'          => 'delete'
            ],

            'chapter_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'qursus\Chapter',
                'description'       => 'Chapter the page relates to, if any.',
                'ondelete'          => 'cascade',         // delete chapter when parent module is deleted
                'onupdate'          => 'onupdateChapterId'
            ],

            'section_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'qursus\Section',
                'description'       => 'Section the page relates to, if any.',
                'ondelete'          => 'cascade'         // delete chapter when parent module is deleted
            ]

        ];
    }

    /**
     * Calculates the next active items based on the given object manager, object IDs, and language.
     *
     * @param ObjectManager $om The object manager used to read the data.
     * @param array $oids The array of object IDs.
     * @param string $lang The language used to read the data.
     * @return array The array of next active items.
     */
    // public static function calcNextActive($om, $oids, $lang)
    // {
    //     $result = [];

    //     $pages = $om->read(__CLASS__, $oids, ['identifier', 'next_active_rule'], $lang);

    //     foreach ($pages as $oid => $page) {
    //         if ($page['next_active_rule'] == 'always visible') {
    //             $result[$oid] = "[]";
    //         } else {
    //             $rule = str_replace('$identifier', $page['identifier'], $page['next_active_rule']);
    //             list($operand, $operator, $value) = explode(' ', $rule);
    //             if (!is_numeric($value) && !in_array($value, ['true', 'false'])) {
    //                 $value = "'$value'";
    //             }
    //             $result[$oid] = "['$operand','$operator',$value]";
    //         }
    //     }
    //     return $result;
    // }

    /**
     * Calculates the next active items based on the last Page collection.
     * @param \equal\orm\Collection $self  An instance of a Page collection.
     */
    public static function calcNextActive($self)
    {
        $result = [];

        $pages = $self->read(['identifier', 'next_active_rule']);

        foreach ($pages as $index => $page) {
            if ($page['next_active_rule'] == 'always visible') {
                $result[$index] = "[]";
            } else {

                $rule = str_replace('$identifier', $page['identifier'], $page['next_active_rule']);

                list($operand, $operator, $value) = explode(' ', $rule);
                if (!is_numeric($value) && !in_array($value, ['true', 'false'])) {
                    $value = "'$value'";
                }
                $result[$index] = "['$operand','$operator',$value]";
            }
        }

        return $result;
    }

    /**
     * Updates the next active value of a given set of objects in the database.
     *
     * @param object $om The object manager to use for writing to the database.
     * @param array $oids The array of object IDs to update.
     * @param array $values The array of values to update the objects with.
     * @param string $lang The language code of the objects' language.
     * @throws Exception If there is an error while writing to the database.
     * @return void
     */
    public static function onupdateNextActive($om, $oids, $values, $lang)
    {
        $om->update(__CLASS__, $oids, ['next_active' => null], $lang);
    }


    /**
     * Update the chapter ID for a given set of objects.
     *
     * @param object_manager $om The object manager instance.
     * @param array $oids An array of object IDs.
     * @param array $values An array of values.
     * @param string $lang The language.
     * @throws Exception If an error occurs.
     * @return void
     */
    public static function onupdateChapterId($om, $oids, $values, $lang)
    {
        $pages = $om->read(__CLASS__, $oids, ['chapter_id'], $lang);

        foreach ($pages as $oid => $page) {
            Chapter::onupdatePagesIds($om, $page['chapter_id'], $values, $lang);
        }
    }
}
