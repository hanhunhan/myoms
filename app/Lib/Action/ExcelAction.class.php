<?php
/**
 * Excel�����
 * Created by xuke.
 * User: Administrator
 * Date: 2016/4/18
 * Time: 9:02
 */
import('Org.Io.Mylog');

class ExcelAction extends Action {
    /**
     * ��ʾҳ��
     */
    const STEP_SHOW_PAGE = 1;

    /**
     * ����ҵ��
     */
    const STEP_PROCESS_BUSINESS = 2;

    const ENCRYPTION_CODE = 'uriswt8749';

    const EXT_XLS = 'xls';

    const EXT_XLSX = 'xlsx';

    protected $dataStart = array(
        'x' => 'A',
        'y' => 6
    );

    protected $dataEnd = array(
        'x' => 'AS',
        'y' => 54
    );

    protected $columnNames = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
    );

    protected $businessType = '';

    function __construct() {
        parent::__construct();
        $this->businessType = isset($_REQUEST['businessType'])?trim($_REQUEST['businessType']):'reim';
    }

    /**
     * ��excel�е�������
     */
    public function importProjectFees() {
        $step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : self::STEP_SHOW_PAGE;
        if ($step == self::STEP_SHOW_PAGE) {
            // չʾ����
            $this->assign('postUrl', U('Excel/importProjectFees', array('step' => self::STEP_PROCESS_BUSINESS,'businessType'=>$this->businessType)));
            $this->display('import_project_fees');
        } else if ($step == self::STEP_PROCESS_BUSINESS) {
            if (trim($_REQUEST['password']) != self::ENCRYPTION_CODE) {
                die('�������');
            }

            // ����ҵ��
            if ($_FILES && $_FILES['data_file']) {
                $fileExt = mb_strtolower(pathinfo($_FILES['data_file']['name'], PATHINFO_EXTENSION));
                if ($fileExt != self::EXT_XLS && $fileExt != self::EXT_XLSX) {
                    die('����excel�ļ����������ϴ�');
                }
                $this->importExcelData($_FILES['data_file']['tmp_name'], $_REQUEST['city']);
            } else {
                die('δ�ϴ��ļ�');
            }
        }
    }

    private function checkFileExist($fileEntity, &$phpReader) {
        // �����ⲿ��
        Vendor('phpExcel.PHPExcel');
        Vendor('phpExcel.IOFactory.php');
        Vendor('phpExcel.Reader.Excel5.php');
        Vendor('Oms.ExcelRow');
        $phpReader = new PHPExcel_Reader_Excel2007();
        if (!$phpReader->canRead($fileEntity)) {
            $phpReader = new PHPExcel_Reader_Excel5();
            if (!$phpReader->canRead($fileEntity)) {
                die('excel�ļ�������');
            }
        }
    }

    private function excelInit(){
        switch($this->businessType){
            case 'reim':
                break;
            case 'transMember':
                $this->dataStart = array(
                    'x' => 'A',
                    'y' => 2
                );

                $this->dataEnd = array(
                    'x' => 'L',
                    'y' => 102
                );

                $this->columnNames = array(
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J','K','L',
                );

                //�������ĸ�case
                $this->fromCase = 2322;
                //ת�Ƶ��ĸ�case
                $this->toCase = 2360;
                break;
        }

    }

    private function importExcelData($fileEntity, $city) {
        set_time_limit(0);
        $this->checkFileExist($fileEntity, $phpReader);  // ����ļ��Ƿ����
        MyLog::write('excel�ļ���ʽ�����ϣ�');

        $this->excelInit();

        try {
            $phpExcel = $phpReader->load($fileEntity);
            $activeSheet = $phpExcel->getSheet(0);
            $this->dataEnd = array(
                'x' => $activeSheet->getHighestDataColumn(),
                'y' => $activeSheet->getHighestDataRow()
            );
            for ($i = $this->dataStart['y']; $i <= $this->dataEnd['y']; $i++) {
                $row = ExcelRow::instance();
                Mylog::write(sprintf('��ʼ�����%s�����ݣ�', $i - $this->dataStart['y'] + 1));

                //die($this->businessType);

                switch($this->businessType) {
                    case 'reim':
                        foreach ($this->columnNames as $index => $letter) {
                            $row->setColumn($letter, $activeSheet->getCellByColumnAndRow($index, $i)->getValue());
                        }

                        $row->saveDataToDB($city);
                        break;
                    case 'transMember':
                        foreach ($this->columnNames as $index => $letter) {
                            if($letter=='L')
                                $receiptNo = $activeSheet->getCellByColumnAndRow($index, $i)->getValue();
                        }

                        $row->transMember($receiptNo,$city,$this->fromCase,$this->toCase);
                        break;
                }

                Mylog::write('�������<br/>');
            }
        } catch (Exception $e) {
            MyLog::write(sprintf('�����룺%s, ����ԭ��%s', $e->getCode(), $e->getMessage()));
        }
    }

    public function validatePassword() {
        $password = trim($_REQUEST['code']);
        if (empty($password)) {
            echo json_encode(array(
                'code' => -1,
                'msg' => g2u('���벻��Ϊ��')
            ));
        } else if ($password != self::ENCRYPTION_CODE) {
            echo json_encode(array(
                'code' => 1,
                'msg' => g2u('�������')
            ));
        } else {
            echo json_encode(array(
                'code' => 0,
                'msg' => g2u('������ȷ')
            ));
        }
    }

}