<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Order\Status\AdminOrderStatusFilterFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ORDER)]
class OrderStatusFilterController extends AdminBaseController
{
    public function __construct(
        protected readonly AdminOrderStatusFilterFacade $adminOrderStatusFilterFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    #[CanView]
    public function orderStatusFilterSelectAction(): Response
    {
        return $this->render('@ShopsysAdministration/partial/quick_order_status_filter_select.html.twig', [
            'orderStatuses' => $this->orderStatusFacade->getAll(),
            'selectedOrderStatus' => $this->adminOrderStatusFilterFacade->getSelectedOrderStatus(),
        ]);
    }

    #[Route(path: '/order/filter-status/{orderStatusId}', requirements: ['orderStatusId' => '\d+'])]
    #[CanView]
    public function selectOrderStatusAction(Request $request, ?int $orderStatusId = null): RedirectResponse
    {
        $this->adminOrderStatusFilterFacade->setSelectedOrderStatusId($orderStatusId);

        $referer = $request->server->get('HTTP_REFERER');
        $refererParts = $referer === null ? false : parse_url($referer);

        if ($refererParts === false
            || ($refererParts['host'] ?? null) !== $request->getHost()
            || !$this->isSafeRefererPath($refererParts['path'] ?? '')
        ) {
            return $this->redirectToRoute('admin_order_list');
        }

        return $this->redirect($this->removeGridPagingFromUrl($referer));
    }

    protected function isSafeRefererPath(string $path): bool
    {
        return str_starts_with($path, '/')
            && !str_starts_with($path, '//')
            && !str_starts_with($path, '/\\');
    }

    /**
     * The grid clamps an out-of-range page itself, but the number kept from the referer would stay in the address bar.
     */
    protected function removeGridPagingFromUrl(string $url): string
    {
        $urlParts = parse_url($url);
        parse_str($urlParts['query'] ?? '', $queryParameters);

        $gridsParameters = $queryParameters[Grid::GET_PARAMETER] ?? null;

        if (is_array($gridsParameters)) {
            foreach ($gridsParameters as $gridId => $gridParameters) {
                if (is_array($gridParameters)) {
                    unset($gridParameters['page']);
                    $queryParameters[Grid::GET_PARAMETER][$gridId] = $gridParameters;
                }
            }
        }

        $path = $urlParts['path'] ?? '/';
        $query = http_build_query($queryParameters);

        return $query === '' ? $path : $path . '?' . $query;
    }
}
