<?=$php?>


declare(strict_types=1);

namespace <?=$namespace?>;

use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use Sys\Model\MysqlModel;

class <?=$classname?> extends MysqlModel
{
    public function __construct(protected IQueryBuilderHandler $qb)
    {
        parent::__construct($qb);
    }

    public function find(int $id){}

    public function get(){}
}
