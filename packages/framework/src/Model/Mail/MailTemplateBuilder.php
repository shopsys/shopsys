<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailTemplateBuilder
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade
     */
    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly Domain $domain,
        protected readonly CdnFacade $cdnFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getContentBaseUrl(int $domainId): string
    {
        return $this->cdnFacade->resolveDomainUrlForAssets($this->domain->getDomainConfigById($domainId));
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterText(int $domainId): string
    {
        return $this->mailSettingFacade->getFooterTextUrl($domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterIcons(int $domainId): string
    {
        $footerIconsHtml = '';
        $itemPadding = '';

        foreach ($this->mailSettingFacade->getFooterIconUrls($domainId) as $footerIconName => $footerIconUrl) {
            if ($footerIconUrl === null) {
                continue;
            }

            $footerIconsHtml .= <<<EOT
                <a href="{$footerIconUrl}" style="border:none;text-decoration:none;padding: 0 10px;" target="_blank">
                    <img alt="{$footerIconName}" border="0" src="{$this->getContentBaseUrl($domainId)}/public/frontend/mail/{$footerIconName}.png" width="32" height="32" style="color: black; width: 32px; height: 32px;">
                </a>
            EOT;
        }

        return $footerIconsHtml;
    }

    /**
     * @param int $domainId
     * @param string|null $content
     * @return string
     */
    public function getMailTemplateWithContent(int $domainId, ?string $content = ''): string
    {
        return <<<EOT
            <head>
                <style type="text/css">
                    body,
                    html {
                        margin: 0;
                        padding: 0;
                        width: 100% !important;
                        background-color: #F2F2F2;
                    }

                    body,
                    table,
                    tbody,
                    td,
                    tr {
                        -webkit-text-size-adjust: 100%;
                        -ms-text-size-adjust: 100%;
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                    }


                    h1,
                    h2,
                    h3,
                    h4,
                    h5,
                    h6 {
                        font-family: Arial, Helvetica, sans-serif;
                        margin-top: 0;
                        line-height: 1.1;
                    }

                    td,
                    p,
                    span,
                    strong,
                    a {
                        font-family: Arial, Helvetica, sans-serif;
                        line-height: 1.3;
                    }

                    .container {
                        width: 100% !important;
                        max-width: 700px !important;
                        margin: 0 auto !important;
                        display: block !important;
                    }

                    .header {
                        padding: 30px 30px 0 30px;
                    }

                    .main {
                        padding: 30px;
                    }

                    .footer {
                        padding: 30px 20px 0 20px;
                    }

                    .order-addresses {
                        width: 100%;
                        display: block;
                    }

                    .product-description {
                        width: 100%;
                        display: block;
                    }

                    .product-price {
                        width: 100%;
                        display: block;
                    }

                    .content {
                        background-color: #fff;
                        -webkit-box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
                        -moz-box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
                        box-shadow: 1px 4px 11px 0px rgba(0, 0, 0, 0.15);
                    }

                    @media only screen and (min-width: 700px) {
                        .order-addresses {
                            width: 50% !important;
                            display: table-cell !important;
                        }
                    }

                    @media only screen and (min-width: 700px) {
                        .product-description {
                            width: 70% !important;
                            display: table-cell !important;
                        }

                        .product-price {
                            width: 30% !important;
                            display: table-cell !important;
                        }
                    }
                </style>
            </head>
            <body style="margin: 0;">
                <div class="container">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="width:100%;">
                        <tbody>
                            <tr>
                                <td style="direction:ltr;font-size:0px;padding:10px;text-align:center;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="container">
                    <div class="content">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="; min-height: 100px;">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="header">
                                            <img src="{$this->getContentBaseUrl($domainId)}/public/frontend/mail/logo.png" style="height: 50px;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div data-gjs-type="editable" class="gjs-editable main">
                                            {$content}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="container">
                    <div class="footer">
                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
                            <tbody>
                                <tr>
                                    <td>
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"> 
                                            <tr> 
                                                <td style="text-align: center;">
                                                    {$this->getFooterIcons($domainId)}
                                                </td>
                                            </tr> 
                                        </table>
                                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%"> 
                                            <tr>
                                                <td style="text-align: center; padding: 10px;">
                                                    {$this->getFooterText($domainId)}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="container">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%" style="width:100%;">
                        <tbody>
                            <tr>
                                <td style="direction:ltr;font-size:0px;padding:10px;text-align:center;">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </body>
        EOT;
    }
}
