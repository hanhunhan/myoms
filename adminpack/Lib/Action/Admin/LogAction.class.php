<?php

class LogAction extends ExtendAction {

    /**
     * 查询log日志
     */
    function logInfo() {
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(199);
        $form->orderField = "ID DESC";

        $formHtml = $form->getResult();

        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 保存上次检索的结果
        $this->assign('form', $formHtml);
        $this->display('logInfo');
    }
}

?>