<?php
/**
 * Excel表格处理
 * Created by xuke.
 * User: Administrator
 * Date: 2016/4/18
 * Time: 9:02
 */
import('Org.Io.Mylog');

class ExcelAction extends Action {
    /**
     * 显示页面
     */
    const STEP_SHOW_PAGE = 1;

    /**
     * 处理业务
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
     * 从excel中导入数据
     */
    public function importProjectFees() {
        $step = !empty($_REQUEST['step']) ? $_REQUEST['step'] : self::STEP_SHOW_PAGE;
        if ($step == self::STEP_SHOW_PAGE) {
            // 展示界面
            $this->assign('postUrl', U('Excel/importProjectFees', array('step' => self::STEP_PROCESS_BUSINESS,'businessType'=>$this->businessType)));
            $this->display('import_project_fees');
        } else if ($step == self::STEP_PROCESS_BUSINESS) {
            if (trim($_REQUEST['password']) != self::ENCRYPTION_CODE) {
                die('密码错误');
            }

            // 处理业务
            if ($_FILES && $_FILES['data_file']) {
                $fileExt = mb_strtolower(pathinfo($_FILES['data_file']['name'], PATHINFO_EXTENSION));
                if ($fileExt != self::EXT_XLS && $fileExt != self::EXT_XLSX) {
                    die('不是excel文件，请重新上传');
                }
                $this->importExcelData($_FILES['data_file']['tmp_name'], $_REQUEST['city']);
            } else {
                die('未上传文件');
            }
        }
    }

    private function checkFileExist($fileEntity, &$phpReader) {
        // 导入外部库
        Vendor('phpExcel.PHPExcel');
        Vendor('phpExcel.IOFactory.php');
        Vendor('phpExcel.Reader.Excel5.php');
        Vendor('Oms.ExcelRow');
        $phpReader = new PHPExcel_Reader_Excel2007();
        if (!$phpReader->canRead($fileEntity)) {
            $phpReader = new PHPExcel_Reader_Excel5();
            if (!$phpReader->canRead($fileEntity)) {
                die('excel文件不存在');
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

                //来自于哪个case
                $this->fromCase = 2322;
                //转移到哪个case
                $this->toCase = 2360;
                break;
        }

    }

    private function importExcelData($fileEntity, $city) {
        set_time_limit(0);
        $this->checkFileExist($fileEntity, $phpReader);  // 检查文件是否存在
        MyLog::write('excel文件格式检测完毕！');

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
                Mylog::write(sprintf('开始导入第%s条数据：', $i - $this->dataStart['y'] + 1));

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

                Mylog::write('导入结束<br/>');
            }
        } catch (Exception $e) {
            MyLog::write(sprintf('错误码：%s, 错误原因：%s', $e->getCode(), $e->getMessage()));
        }
    }

    public function validatePassword() {
        $password = trim($_REQUEST['code']);
        if (empty($password)) {
            echo json_encode(array(
                'code' => -1,
                'msg' => g2u('密码不能为空')
            ));
        } else if ($password != self::ENCRYPTION_CODE) {
            echo json_encode(array(
                'code' => 1,
                'msg' => g2u('密码错误')
            ));
        } else {
            echo json_encode(array(
                'code' => 0,
                'msg' => g2u('密码正确')
            ));
        }
    }

}