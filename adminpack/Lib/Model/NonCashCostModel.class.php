<?php

/**
 * ���ֽ�֧��Model
 * User: xuke
 * Date: 2016/3/7
 * Time: 17:17
 */
class NonCashCostModel extends Model {
    /**
     * ��ǰ׺
     * @var string
     */
    protected $tablePrefix = 'ERP_';

    /**
     * ����
     * @var string
     */
    protected $tableName = 'NONCASHCOST';

    /**
     * ��Ӽ�¼
     * @param $data
     * @return bool|int|mixed
     */
    public function addRecord($data) {
        $insertedId = 0;
        if (is_array($data) && !empty($data)) {
            // �����������ز���ID
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