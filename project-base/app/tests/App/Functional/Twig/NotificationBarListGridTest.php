<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Tests\App\Test\FunctionalTestCase;
use Twig\Environment;

final class NotificationBarListGridTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private Environment $twig;

    public function testTextCellKeepsFormattingAndRemovesScripts(): void
    {
        $template = $this->twig->load('@ShopsysAdministration/content/notificationBar/listGrid.html.twig');

        $renderedCell = $template->renderBlock('grid_value_cell_id_text', [
            'value' => '<p><strong>Sale</strong></p><img src="x" onerror="alert(1)"><script>alert(1)</script>',
        ]);

        $this->assertStringContainsString('<p><strong>Sale</strong></p>', $renderedCell);
        $this->assertStringNotContainsString('onerror', $renderedCell);
        $this->assertStringNotContainsString('<script', $renderedCell);
    }
}
