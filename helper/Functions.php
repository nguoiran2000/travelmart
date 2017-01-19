<?php
namespace app\helper;

use Yii;

class Functions{

    /* --
     - return full website link
     --*/
    public static function url(){
        if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . Yii::$app->getRequest()->serverName;
    }

    public static function toSlug($str){
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );

        foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $str);
        $clean = strtolower(trim($clean));
        $clean = preg_replace("/[\/_|+ -]+/", '-', $clean);
        return $clean;
    }

    public static function cityAll()
    {
        $provinces = (new \yii\db\Query())
            ->select('province.provinceid, province.name as province_name, province.type as province_type, district.districtid, district.name as district_name, district.type as district_type')
            ->from('province')
            ->leftJoin('district', 'province.provinceid = district.provinceid')
            ->orderBy('province.name')
            ->all();

        $city = [];
        foreach ($provinces as $key => $value) {
            if(!isset($city[$value['provinceid']])) {
                $city[$value['provinceid']] = [
                    'id' => $value['provinceid'],
                    'name' => $value['province_name'],
                    'type' => $value['province_type']];
                $city[$value['provinceid']]['district'] = [];
            }

            $city[$value['provinceid']]['district'][] = [
                'id' => $value['districtid'],
                'name' => $value['district_name'], 
                'type' => $value['district_type']];
        }

        return array_values($city);
    }
}