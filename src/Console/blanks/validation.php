<?=$php?>


declare(strict_types=1);

namespace <?=$namespace?>;

use Az\Validation\Middleware\ApiValidationMiddleware;

class <?=$classname?> extends ApiValidationMiddleware
{
    protected function setRules($request)
    {
        $this->validation->rule('fieldname', 'required|minLength(8)');
    }
}
