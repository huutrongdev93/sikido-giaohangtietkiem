<?php
Class GHTK_Branch_Custom {
    function __construct() {
        add_filter('admin_branch_form', 'GHTK_Branch_Custom::Form', 20, 2);
        add_filter('pre_insert_branch_data', 'GHTK_Branch_Custom::Insert', 20, 3);
        add_filter('admin_branch_save_validation', 'GHTK_Branch_Custom::Validation', 20, 3);
    }

    public static function Form($form, $branch) {

        $picks = CacheHandler::get('GHTK_Branch');

        if(!have_posts($picks)) {

            $picksResponse = GHTK()->getsPick();

            $picks = [0 => 'Chọn chi nhánh GHTK'];

            if(!empty($picksResponse->success) && !empty($picksResponse->data)) {
                foreach ($picksResponse->data as $datum) {
                    $picks[$datum->pick_address_id] = '#'.$datum->pick_address_id.'-'.$datum->pick_name;
                }
            }

            CacheHandler::save('GHTK_Branch', $picks, 30*24*60*60);
        }

        if(have_posts($branch)) {
            $form->add('branch[ghtk_name]', 'text', ['id' => 'branch_'.$branch->id.'_ghtk_name',  'label' => 'GHTK - Tên người đại diện',
                'after' => '<div class="col-md-6"><div class="form-group">', 'before' => '</div></div>'
            ], $branch->ghtk_name);
            $form->add('branch[ghtk_id]', 'select', ['id' => 'branch_'.$branch->id.'_ghtk_id',  'label' => 'GHTK - Mã kho',
                'options' => $picks,
                'after' => '<div class="col-md-6"><div class="form-group">', 'before' => '</div></div>'
            ], $branch->ghtk_id);
        }
        else {
            $form->add('branch[ghtk_name]', 'text', ['label' => 'Tên người đại diện',
                'after' => '<div class="col-md-6"><div class="form-group">', 'before' => '</div></div>'
            ], '');
            $form->add('branch[ghtk_id]', 'select', ['label' => 'GHTK - Mã kho',
                'options' => $picks,
                'after' => '<div class="col-md-6"><div class="form-group">', 'before' => '</div></div>'
            ]);
        }

        return $form;
    }

    public static function Insert($data, $branch, $old_branch) {

        if(have_posts($old_branch)) {
            $GHTK_id = (!isset($branch['ghtk_id'])) ? $old_branch->ghtk_id : Str::clear($branch['ghtk_id']);
            $GHTK_name = (!empty($branch['ghtk_name'])) ? Str::clear($branch['ghtk_name']) : $old_branch->ghtk_name;
        }
        else {
            $GHTK_id = (!isset($branch['ghtk_id'])) ? '' : Str::clear($branch['ghtk_id']);
            $GHTK_name = (!empty($branch['ghtk_name'])) ? Str::clear($branch['ghtk_name']) : '';
        }

        $data['ghtk_id'] = $GHTK_id;

        $data['ghtk_name'] = $GHTK_name;

        return $data;
    }

    public static function Validation($error, $branch, $branch_old) {

        if(have_posts($branch_old)) {
            if(empty($branch_old->ghtk_id) && empty($branch['ghtk_id'])) {
                return new SKD_Error( 'invalid_branch_ghtk_id', __( 'Mã kho GHTK không được để trống.'));
            }

            if(empty($branch_old->ghtk_name) && empty($branch['ghtk_name'])) {
                return new SKD_Error( 'invalid_branch_ghtk_name', __( 'Tên người đại diện không được để trống.'));
            }
        }
        else {
            if(empty($branch['ghtk_id'])) {
                return new SKD_Error( 'invalid_branch_ghtk_id', __( 'Mã kho GHTK không được để trống.'));
            }
            if(empty($branch['ghtk_name'])) {
                return new SKD_Error( 'invalid_branch_ghtk_name', __( 'Tên người đại diện không được để trống.'));
            }
        }

        return $error;
    }
}