<?php 

namespace App\Consts;

/**
 * ユーザー関連定数クラス
 * @since 2022-7-3
 * @version 1.0.0
 */
class ConstCrudBase{
    
    /** 権限情報 */
    public const AUTHORITY_INFO = [
        'master'=>[
            'name'=>'master',
            'wamei'=>'マスター',
            'level'=>41,
        ],
        'developer'=>[
            'name'=>'developer',
            'wamei'=>'開発者',
            'level'=>40,
        ],
        'admin'=>[
            'name'=>'admin',
            'wamei'=>'管理者',
            'level'=>30,
        ],
        'client'=>[
            'name'=>'client',
            'wamei'=>'クライアント',
            'level'=>20,
        ],
        'oparator'=>[
            'name'=>'oparator',
            'wamei'=>'オペレータ',
            'level'=>10,
        ]
    ];
    
    public function __construct(){

    }
    

//     public const CRUD_BASE_ROOT = dirname(__FILE__) . DIRECTORY_SEPARATOR; // プロジェクトディレクトリの絶対ルートパス。 例→"C:\Users\user\git\CrudBase\laravel7\dev\"
    
    
//     $crud_base_root = dirname(__FILE__) . DIRECTORY_SEPARATOR;
//     define('CRUD_BASE_ROOT', $crud_base_root);
    
    
}