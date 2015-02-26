<?php
namespace amilna\versioning\models;

use creocoder\nestedsets\NestedSetsQueryBehavior;

class VersionQuery extends \yii\db\ActiveQuery
{
    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}

?>
