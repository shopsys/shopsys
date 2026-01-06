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
     * @param string $imageNameWithExtension
     * @return string
     */
    public function getMailImageSrc(int $domainId, string $imageNameWithExtension): string
    {
        return $this->cdnFacade->resolveDomainUrlForAssets($this->domain->getDomainConfigById($domainId)) . '/public/frontend/mail/' . $imageNameWithExtension;
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterTextTableRow(int $domainId): string
    {
        $footerTextTableRow = '';
        $footerText = $this->getFooterText($domainId);

        if ($footerText !== null) {
            $footerTextTableRow = <<<EOT
                <tr>
                    <td style="padding:10px; text-align:center;">
                        <div style="max-width:600px; margin:0 auto;">
                            <span style="font-size:12px; line-height:1.3; mso-line-height-rule:exactly;">
                                {$footerText}
                            </span>
                        </div>
                    </td>
                </tr>
            EOT;
        }

        return $footerTextTableRow;
    }

    /**
     * @param int $domainId
     * @return string|null
     */
    protected function getFooterText(int $domainId): ?string
    {
        return $this->mailSettingFacade->getFooterText($domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterIconsTableRow(int $domainId): string
    {
        $footerIconsTableRow = '';
        $footerIconsHtml = $this->getFooterIcons($domainId);

        if ($footerIconsHtml !== '') {
            $footerIconsTableRow = <<<EOT
                <tr>
                    <td style="padding:10px; text-align:center;">
                        {$footerIconsHtml}
                    </td>
                </tr>
            EOT;
        }

        return $footerIconsTableRow;
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterIcons(int $domainId): string
    {
        $footerIconsHtml = '';

        foreach ($this->mailSettingFacade->getFooterIconUrls($domainId) as $footerIconName => $footerIconUrl) {
            if ($footerIconUrl === null) {
                continue;
            }

            $footerIconNameWithExtension = $footerIconName . '.png';
            $footerIconsHtml .= <<<EOT
                <a href="{$footerIconUrl}" style="border:none;text-decoration:none !important;padding: 0 10px;" target="_blank">
                    <img alt="{$footerIconName}" border="0" src="{$this->getMailImageSrc($domainId, $footerIconNameWithExtension)}" width="32" height="32" style="width: 32px; height: 32px;">
                </a>
                &nbsp;
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
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <!--[if !mso]><!-->
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <!--<![endif]-->
                <!--[if gte mso 9]>
                <xml>
                    <o:OfficeDocumentSettings>
                        <o:AllowPNG/>
                        <o:PixelsPerInch>96</o:PixelsPerInch>
                    </o:OfficeDocumentSettings>
                </xml>
                <![endif]-->
                <style type="text/css">
                    /* Base reset */
                    body, html {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        background-color: #F2F2F2 !important;
                        -webkit-text-size-adjust: 100%;
                        -ms-text-size-adjust: 100%;
                    }
                    
                    /* Typography */
                    h1, h2, h3, h4, h5, h6 {
                        font-family: Arial, Helvetica, sans-serif !important;
                        margin-top: 0 !important;
                        margin-bottom: 10px !important;
                        line-height: 1.1 !important;
                        mso-line-height-rule: exactly;
                    }
                    
                    td, p, span, strong, a {
                        font-family: Arial, Helvetica, sans-serif !important;
                        line-height: 1.3 !important;
                        mso-line-height-rule: exactly;
                    }
                    
                    a {
                        color: #000000;
                        text-decoration: underline;
                    }
                    
                    /* Layout */
                    .container-wrapper {
                        width: 100% !important;
                        max-width: 700px !important;
                        margin: 0 auto !important;
                    }
                    
                    .content-block {
                        background-color: #ffffff !important;
                        color: #000000 !important;
                        box-shadow: 1px 4px 11px 0px rgba(0,0,0,0.15);
                    }
                    
                    /* Client-specific fixes */
                    .ExternalClass, .ReadMsgBody { width: 100%; }
                    .ExternalClass, .ExternalClass p, .ExternalClass span,
                    .ExternalClass font, .ExternalClass td, .ExternalClass div { line-height: 100%; }
                    #outlook a { padding:0; }
                    table { mso-table-lspace:0pt; mso-table-rspace:0pt; border-collapse: collapse; }
                    img { 
                        -ms-interpolation-mode: bicubic;
                        border: 0;
                        height: auto;
                        max-width: 100%;
                        line-height: 100%;
                        outline: none;
                        text-decoration: none;
                    }
                    
                    /* Responsive */
                    @media only screen and (max-width: 700px) {
                        .mobile-full-width {
                            width: 100% !important;
                            display: block !important;
                        }
                        .order-addresses, .product-description, .product-price {
                            width: 100% !important;
                            display: block !important;
                        }
                    }
                </style>
                <!--[if mso]>
                <style type="text/css">
                    .container-wrapper { width:700px !important; }
                    h1 { font-size:22px !important; }
                    h2 { font-size:20px !important; }
                    h3 { font-size:18px !important; }
                    p, td { font-size:16px !important; }
                    .content-table { width:700px !important; }
                </style>
                <![endif]-->
            </head>
            <body style="margin:0; padding:0; -webkit-text-size-adjust:none;">
                <!--[if mso]>
                <div style="mso-hide:all">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="700" align="center"><tr><td>
                <![endif]-->
                
                <!-- Preheader -->
                <div style="display:none; max-height:0; overflow:hidden; mso-hide:all;">
                    &nbsp;
                </div>

                <!-- Main container -->
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding:20px 0;">
                            <!--[if mso]>
                            <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="700">
                            <tr><td>
                            <![endif]-->
                            
                            <div class="container-wrapper">
                                <table role="presentation" class="content-block" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="padding:30px 0 0 30px;">
                                            <img src="{$this->getMailImageSrc($domainId, 'logo.png')}" height="50" alt="Company Logo" style="display:block; height:50px; width:auto;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:30px;">
                                            <div style="max-width:600px; margin:0 auto;" data-gjs-type="editable" class="gjs-editable main">
                                                <!--[if mso]>
                                                <table role="presentation" border="0" cellspacing="0" cellpadding="0"><tr><td>
                                                <![endif]-->
                                                {$content}
                                                <!--[if mso]>
                                                </td></tr></table>
                                                <![endif]-->
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            &nbsp;
                                        </td>
                                    </tr>
                                    {$this->getFooterIconsTableRow($domainId)}
                                    {$this->getFooterTextTableRow($domainId)}
                                </table>
                            </div>

                            <!--[if mso]>
                            </td></tr></table>
                            <![endif]-->
                        </td>
                    </tr>
                </table>
                
                <!--[if mso]>
                </td></tr></table>
                </div>
                <![endif]-->
            </body>
        EOT;
    }
}
