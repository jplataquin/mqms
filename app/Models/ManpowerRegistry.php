<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManpowerRegistry extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    public function getName(){

        $name = trim($this->firstname.' '.$this->middlename.' '.$this->lastname.' '.$this->suffix);

        $name = preg_replace('/\s+/', ' ', $name);

        return $name;
    }


    public static function region_options(){
        
        return [
            'VI____' => 'Western Visayas VI'
        ];
    }

    public static function province_options(){
        return [
            'VI____' => [
                'AKLAN_'     => 'Aklan',
                'ANTIQU'     => 'Antique',
                'CAPIZ_'     => 'Capiz',
                'GUIMAR'     => 'Guimaras',
                'ILOILO'     => 'Iloilo',
                'NEGOCC'     => 'Negros Occidental'
            ]
        ];
    }


    public static function city_municipality_options(){
        return [
            'AKLAN_'     => [
                'ALTAVA' => 'Altavas', 
                'BELETE' => 'Balete',
                'BANGA_' => 'Banga',
                'BATAN_' => 'Batan',
                'BURANG' => 'Buruanga',
                'IBAJAY' => 'Ibajay',
                'KALIBO' => 'Kalibo',
                'LEZO__' => 'Lezo',
                'LIBACA' => 'Libacao',
                'MADALA' => 'Madalag',
                'MAKATO' => 'Makato',
                'MALAY_' => 'Malay',
                'MALINA' => 'Malinao',
                'NABAS_' => 'Nabas',
                'NEWWAS' => 'New Washington',
                'NUMANC' => 'Numancia',
                'TANGAL' => 'Tangalan'
            ],
            'ANTIQU'     => [
                 'ANINIY' => 'Anini-y', 
                 'BARBAZ' => 'Barbaza', 
                 'BELSON' => 'Belison', 
                 'BUGASO' => 'Bugasong', 
                 'CALUYA' => 'Caluya', 
                 'CULASI' => 'Culasi', 
                 'HAMTIC' => 'Hamtic', 
                 'LAUAAN' => 'Laua-an', 
                 'LIBERT' => 'Libertad', 
                 'PANDAN' => 'Pandan', 
                 'PATNON' => 'Patnongon', 
                 'SANJOS' => 'San Jose de Buenavista', 
                 'SANREM' => 'San Remigio', 
                 'SEBAST' => 'Sebaste', 
                 'SIBALO' => 'Sibalom', 
                 'TIBIAO' => 'Tibiao', 
                 'TOBIAS' => 'Tobias Fornier',
                 'VALDER' => 'Valderrama'
            ],
            'CAPIZ_'     => [
                'CUARTE' => 'Cuartero', 
                'DAO___' => 'Dao', 
                'DUMALA' => 'Dumalag', 
                'DUMARA' => 'Dumarao', 
                'IVISAN' => 'Ivisan', 
                'JAMIND' => 'Jamindan', 
                'MAAYON' => 'Ma-ayon', 
                'MAMBUS' => 'Mambusao', 
                'PANAY_' => 'Panay', 
                'PANITA' => 'Panitan', 
                'PILAR_' => 'Pilar', 
                'PONTEV' => 'Pontevedra', 
                'ROXAS_' => 'Roxas', 
                'SAPIAN' => 'Sapian', 
                'SIGMA_' => 'Sigma', 
                'TAPAZ_' => 'Tapaz'
            ],
            'GUIMAR'     => [],
            'ILOILO'     => [],
            'NEGOCC'     => []
        
        ];
    }
}