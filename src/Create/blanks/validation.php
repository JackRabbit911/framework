<?=$php?>

namespace <?=$namespace?>;

use Az\Validation\Middleware\ValidationMiddleware;
use Psr\Http\Message\ServerRequestInterface;

final class <?=$classname?> extends ValidationMiddleware
{
    protected function setRules($request)
    {
        $this->validation->rule('fieldname', 'required|minLength(8)');
    }

//    protected function modifyRequest($request, $data): ServerRequestInterface
//    {
//        return $request;
//    }

//    protected function modifyData($data)
//    {
//        return $data;
//    }

//    protected function debug($request, $data)
//    {
//        dd($this->validation->getResponse(), $data);
//    }
}
