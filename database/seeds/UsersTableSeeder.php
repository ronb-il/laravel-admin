<?php

use Illuminate\Database\Seeder;
use App\User;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersList = [
            // Customers
            ["chris.reed@shos.com", "128", "customer-viewer", "vPSMZxN"],
            ["Jessica.Wydra@cdiscount.com","132,147,160","customer-viewer", "RMFN9BUC"],
            ["jeremy.viollin@showroomprive.net","134,141,142","customer-viewer", "Y63nLT8S"],
            ["felipe.pierro@mobly.com.br", "144", "customer-viewer", "czgxnsgC"],
            ["maria.napole@dafiti.com.br", "145", "customer-viewer", "dNaeEYgE"],
            ["cecile.kern-garnier@camif-matelsom.com", "148", "customer-viewer", "kYyStmwY"],
            ["paulo.ricardo@cnova.com", "150", "customer-viewer", "gNwkPsqJ"],
            ["will.vinton@mfrm.com", "162,9", "customer-viewer", "GzXecAxH"],
            ["kate.jobgen@coleman.com", "164", "customer-viewer", "Anf42E9s"],
            ["Ignateva.S@citilink.ru", "165", "customer-viewer", "KaEtZ6nk"],
            ["Potapov.I@citilink.ru", "165", "customer-viewer", "ZaYgmv8U+"],
            ["guillaume.moynot@orange.com", "166", "customer-viewer", "ganQTHZz"],
            ["gil-d@daka90.co.il", "169", "customer-viewer", "JEfND4tb"],
            ["plenkin@220-volt.ru", "170", "customer-viewer", "bGDwsMuF"],
            ["Janine_Rizkalla@gap.com", "172", "customer-viewer", "pz4LCJ9F"],
            ["kzorn@livingdirect.com", "173,186,188,189", "customer-viewer", "fcEXHppY"],
            ["Idan@waves.com", "174", "customer-viewer", "mdhmzqx5"],
            ["nicolas.beyer@camif-matelsom.com", "178", "customer-viewer", "ces4XyL2"],

            // Personali Users
            ["ofir@personali.com", "", "admin", "qwerty"],
            ["hanny@persnali.com", "", "admin", "qwerty"],
            ["alessandro.mayer@personali.com", "", "am-editor", "qwerty"],
            ["alex@personali.com", "", "am-editor", "qwerty"],
            ["keren@personali.com", "", "admin", "qwerty"],
            ["amir.bilu@personali.com", "", "admin", "qwerty"],
            ["nitasn@personali.com", "", "admin", "qwerty"],
            ["oren.hadad@personali.com", "", "admin", "qwerty"],
            ["dan.ofir@personali.com", "", "admin", "qwerty"],
            ["yael.szilas@personali.com", "", "am-editor", "qwerty"],
            ["ilan@personali.com", "", "admin", "qwerty"],
            ["ronen@personali.com", "", "am-editor", "qwerty"],
            ["or@personali.com", "", "admin", "qwerty"],
            ["shemi.jacob@personali.com", "", "am-editor", "qwerty"],
            ["kathryn.hennessey@personali.com", "", "am-editor", "qwerty"],
            ["rick.fawcett@personali.com", "", "am-editor", "qwerty"],
            ["zach@personali.com", "", "am-editor", "qwerty"],
            ["denise.sauser@personali.com", "", "am-editor", "qwerty"],
            ["darren.hitchcock@personali.com", "", "am-editor", "qwerty"],
            ["beatrice@personali.com", "", "am-editor", "qwerty"],
            ["eran.oren@personali.com", "", "am-editor", "qwerty"],
            ["noam@personali.com", "", "am-editor", "qwerty"],
            ["amir.gutman@personali.com", "", "admin", "qwerty"],
            ["ron.b@personali.com", "", "admin", "qwerty"],
        ];

        foreach ($usersList as $userInfo) {
            // store
            $user = new User;
            $user->name = $userInfo[0];
            $user->email = $userInfo[0];

            $permissions = ['roles' => explode(',', $userInfo[2])];

            if (!empty($userInfo[1])) {
                $permissions['affiliates'] = explode(',', $userInfo[1]);
            }

            $password = $userInfo[3];
            if (in_array($userInfo[2], ['admin','am-editor'])) {
                $result = DB::select(DB::raw("SELECT password FROM admin.admin_users WHERE email = '" . $userInfo[0] . "'"));
                if ($result) {
                    $password = $result[0]->password;
                }
            }
            $user->password = $password;

            $user->permissions = $permissions;
            $user->save();
        }
    }
}
