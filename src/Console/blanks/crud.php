<?=$php?>


declare(strict_types=1);

namespace <?=$namespace?>;

use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use Sys\Model\MysqlModel;

class <?=$classname?> extends MysqlModel
{
    private string $table;

    public function __construct(protected IQueryBuilderHandler $qb)
    {
        parent::__construct($qb);
    }

    public function create(array $data){}

    public function read(int $id){}

    public function update(int $id, array $data){}

    public function delete(int $id){}
}
