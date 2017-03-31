<?php

/**
 * 非现金支付Model
 * User: xuke
 * Date: 2016/3/7
 * Time: 17:17
 */
class NonCashCostModel extends Model {
    /**
     * 表前缀
     * @var string
     */
    protected $tablePrefix = 'ERP_';

    /**
     * 表名
     * @var string
     */
    protected $tableName = 'NONCASHCOST';

    /**
     * 添加记录
     * @param $data
     * @return bool|int|mixed
     */
    public function addRecord($data) {
        $insertedId = 0;
        if (is_array($data) && !empty($data)) {
            // 自增主键返回插入ID
            $insertedId = $this->add($data);
        }
        return !empty($insertedId) && $insertedId > 0 ? $insertedId : FALSE;
    }

    public function changeRecord($data,$id)
    {
        if ($id) {
            if (is_array($data) && !empty($data)) {
                $updateNum = $this->where("ID =" . $id)->save($data);
            }
        }
        return !empty($updateNum ) && $updateNum  > 0 ? $updateNum  : FALSE;
    }

}