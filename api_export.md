#import export to excel and csv using maatwebsite example laravel.

It is built:

* Laravel-Excel - [Maatwebsite/Laravel-Excel](https://github.com/Maatwebsite/Laravel-Excel)
* Web Site - [Web](http://www.maatwebsite.nl/laravel-excel/docs)

Step 1: Installation
add line in composer.json in required part
```
"maatwebsite/excel": "~2.1.0"
```
Then, run command
```
$ composer update
```
Now open config/app.php file and add service provider and aliase.
```
'providers' => [
	'Maatwebsite\Excel\ExcelServiceProvider',
],
'aliases' => [
	'Excel' => 'Maatwebsite\Excel\Facades\Excel',
],
```
Config
If you are using Laravel 5 then fire following command:
```
$ php artisan vendor:publish
```
Now open routes/api.php file and add route.
```
$api->get('downloadExcel/{type}', 'App\\Api\\V1\\Controllers\\BookController@downloadExcel');
```
add bellow function in controllers, **app/Api/V1/Controllers/BookController.php**
```
public function downloadExcel($type)
{
    $contactTypes = Book::get()->toArray();
    $contactTypesArray = [];
    // Define the Excel spreadsheet headers
    $contactTypesArray[] = ['NAME','TITLE','AUTHER NAME','PAGE COUNT','IMAGE PATH','BOOK CAT ID','USER ID','CREATED','UPDATED'];
    foreach ($contactTypes as $contactType) {
        $contactTypesArray[] = $contactType;
    }
    if($type == 'excel'){
        Excel::create('ContactTypes', function($excel) use ($contactTypesArray) {
            $excel->setTitle('ContactTypes');
            $excel->setCreator('Auther-Name')->setCompany('Company-Name');
            $excel->setDescription('ContactTypes');
            $excel->sheet('ContactTypes', function($sheet)  use ($contactTypesArray) {
                $sheet->fromArray($contactTypesArray, null, 'A1', false, false);
            });
        })->store('xls');
        return response()->json([
            'status' => 'ok',
            'path' => 'http://'.\Request::server('HTTP_HOST').'/exports/ContactTypes.xls'
        ], 201);
    } else {
        return response()->json([
            'status' => 'error',
            'path' => 'Undefined type.'
        ], 400);
    }
}
```
if export in pdf to add line in composer.json in required part. **Export to PDF**
```
"dompdf/dompdf": "~0.6.1"
```
Then, run command
```
$ composer update
```
add condition in controllers, **app/Api/V1/Controllers/BookController.php**
```
if($type == 'pdf'){
    Excel::create('ContactTypes', function($excel) use ($contactTypesArray) {
        $excel->setTitle('ContactTypes');
        $excel->setCreator('Auther-Name')->setCompany('Company-Name');
        $excel->setDescription('ContactTypes');
        $excel->sheet('ContactTypes', function($sheet)  use ($contactTypesArray) {
            $sheet->fromArray($contactTypesArray, null, 'A1', false, false);
        });
    })->store('pdf');
    return response()->json([

        'status' => 'ok',
        'path' => 'http://'.\Request::server('HTTP_HOST').'/exports/ContactTypes.pdf'
    ], 201);
}
```