<?php
/*
    This file is part of Symbiose Community Edition <https://github.com/yesbabylon/symbiose>
    Some Rights Reserved, Yesbabylon SRL, 2020-2021
    Licensed under GNU AGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace qursus;

use equal\error\Reporter;
use equal\orm\Model;
use equal\services\Container;
use Exception;

class Group extends Model
{

    public static function getColumns()
    {
        return [
            'identifier' => [
                'type'              => 'integer',
                'description'       => 'Unique identifier of the group within the leaf.',
                'onupdate'          => 'onupdateVisibility',
                'default'           => 1
            ],

            'order' => [
                'type'              => 'integer',
                'description'       => 'Position of the group in the leaf.',
                'default'           => 1
            ],

            'direction' => [
                'type'              => 'string',
                'selection'         => ['horizontal', 'vertical'],
                'default'           => 'vertical'
            ],

            'row_span' => [
                'type'              => 'integer',
                'default'           => 1,
                'description'       => "Height of the group, in rows (default = 1, max = 8)."
            ],

            'visible' => [
                'type'              => 'computed',
                'function'          => 'calcVisible',
                'result_type'       => 'string',
                'store'             => true
            ],

            'visibility_rule' => [
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
                'onupdate'          => 'onupdateVisibility'
            ],

            'fixed' => [
                'type'              => 'boolean',
                'default'           => false,
                'description'       => "If true, group is always visible."
            ],

            'widgets' => [
                'type'              => 'alias',
                'alias'             => 'widgets_ids'
            ],

            'widgets_ids' => [
                'type'              => 'one2many',
                'foreign_object'    => 'qursus\Widget',
                'foreign_field'     => 'group_id',
                'order'             => 'order',
                'sort'              => 'asc',
                'ondetach'          => 'delete'
            ],

            'leaf_id' => [
                'type'              => 'many2one',
                'foreign_object'    => 'qursus\Leaf',
                'description'       => 'Leaf the group relates to.',
                'ondelete'          => 'cascade'         // delete group when parent leaf is deleted
            ]

        ];
    }
    /**
     * Calculates the visibility of groups.
     * @param \equal\orm\Collection $self  An instance of a Group collection.
     * @return array<string> The visibility of each group
     */
    public static function calcVisible($self)
    {
        $result = [];

        // file_put_contents(QN_LOG_STORAGE_DIR . '/tmp.log', 'calcVisible' . PHP_EOL, FILE_APPEND | LOCK_EX);

        //Retrieves data from the Group collection.
        $groups = $self->read(['identifier', 'visibility_rule']);

        foreach ($groups as $oid => $group) {
            // $result[$oid] = "['test', '=', 'test']";
            continue;
            if ($group['visibility_rule'] == 'always visible') {
                $result[$oid] = "[]";
            } else {
                // If we have a different pattern like $group['visibility_rule'] == '$page.submitted = true'.
                $rule = str_replace('$identifier', $group['identifier'], $group['visibility_rule']);

                // The visibility_rule is processed and a new array is assigned to the corresponding key.
                list($operand, $operator, $value) = explode(' ', $rule);
                if (!is_numeric($value) && !in_array($value, ['true', 'false'])) {
                    $value = "'$value'";
                }
                $result[$oid] = "['$operand','$operator',$value]";
            }
        }
        return $result;
    }

    /**
     * @param \equal\orm\Collection $self  Collection holding a series of objects of current class.
     */
    public static function onupdateVisibility($self)
    {
        file_put_contents(QN_LOG_STORAGE_DIR . '/tmp.log', 'onupdateVisible' . PHP_EOL, FILE_APPEND | LOCK_EX);
        $self->read(['visible']);
        foreach ($self as $id) {
            Group::id($id)->update(['visible' => null]);
        }
    }
}
