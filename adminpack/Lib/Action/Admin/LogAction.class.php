<?php

class LogAction extends ExtendAction {

    /**
     * ��ѯlog��־
     */
    function logInfo() {
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(199);
        $form->orderField = "ID DESC";

        $formHtml = $form->getResult();

        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����ϴμ����Ľ��
        $this->assign('form', $formHtml);
        $this->display('logInfo');
    }
}

?>