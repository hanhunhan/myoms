<?php

/**
 * Class ActiveBudgetModel
 * 活动预算模型
 */
class ActiveBudgetModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'ACTIBUDGETFEE';

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function onActiveChangeSuccess($projectChangeID) {
        if (empty($projectChangeID)) {
            return;
        }

        $sql = "
            UPDATE ERP_ACTIBUDGETFEE t
            SET t.ISVALID = -1
            WHERE t.ID IN (
                SELECT DISTINCT(BID)
                FROM ERP_CHANGELOG CH
                WHERE CH.CID = {$projectChangeID}
                AND CH.TABLEE = 'ERP_ACTIBUDGETFEE'
            )
        ";

        $this->startTrans();
        $updated = $this->query($sql);
        if ($updated !== false) {
            $this->commit();
            return true;
        } else {
            $this->rollback();
            return false;
        }

    }
    
}

 