<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailTemplateBuilder
{
    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly Domain $domain,
        protected readonly CdnFacade $cdnFacade,
        protected readonly string $mailImagesUrlPrefix,
    ) {
    }

    public function getMailImageSrc(int $domainId, string $imageNameWithExtension): string
    {
        return $this->cdnFacade->resolveDomainUrlForAssets($this->domain->getDomainConfigById($domainId)) . $this->mailImagesUrlPrefix . $imageNameWithExtension;
    }

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

    protected function getFooterText(int $domainId): ?string
    {
        return $this->mailSettingFacade->getFooterText($domainId);
    }

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

    public function getMailTemplateWithContent(int $domainId, ?string $content = ''): string
    {
        return <<<EOT
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
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
                    h1 { font-size:26px; }
                    h2 { font-size:20px; }
                    h3 { font-size:18px; }
                    h1, h2, h3, h4, h5, h6 {
                        font-family: Arial, Helvetica, sans-serif !important;
                        margin-top: 0 !important;
                        margin-bottom: 10px !important;
                        line-height: 1.25;
                        mso-line-height-rule: at-least;
                    }
                    
                    td, p, span, strong, a {
                        font-family: Arial, Helvetica, sans-serif !important;
                        line-height: 1.5;
                        mso-line-height-rule: at-least;
                    }

                    .main { overflow-wrap:anywhere; }
                    
                    a {
                        color: #006bd6;
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
                        color: #202139 !important;
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
                    @media only screen and (max-width: 600px) {
                        .mail-content { padding:24px 16px !important; }
                        .mail-logo { padding:24px 16px !important; }
                        .additional-services { padding-left:24px !important; }
                        .product-description, .product-price { width:100% !important; display:block !important; text-align:left !important; padding:0 !important; }
                        .product-price { padding-top:6px !important; }
                        .order-addresses { padding:0 0 20px !important; }
                        .mobile-full-width {
                            width: 100% !important;
                            display: block !important;
                        }
                        .order-addresses {
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
                    .content-table { width:700px !important; }
                </style>
                <![endif]-->
            </head>
            <body style="margin:0; padding:0; background-color:#F2F2F2; color:#202139; font-family:Arial,Helvetica,sans-serif; font-size:14px; -webkit-text-size-adjust:none;">
                <!-- Main container -->
                <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="center" style="padding:20px 0;">
                            <!--[if mso]>
                            <table role="presentation" border="0" cellspacing="0" cellpadding="0" width="700">
                            <tr><td>
                            <![endif]-->
                            
                            <div class="container-wrapper" style="width:100%; max-width:700px; margin:0 auto;">
                                <table role="presentation" class="content-block" width="100%" bgcolor="#ffffff" border="0" cellspacing="0" cellpadding="0" style="background-color:#ffffff; color:#202139;">
                                    <tr>
                                        <td class="mail-logo" bgcolor="#ffffff" style="padding:24px 40px; background-color:#ffffff;">
                                            <img src="{$this->getMailImageSrc($domainId, 'logo.png')}" height="50" alt="Company Logo" style="display:block; height:50px; width:auto;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="mail-content" style="padding:30px 40px;">
                                            <!--[if mso]>
                                            <table role="presentation" width="620" border="0" cellspacing="0" cellpadding="0"><tr><td>
                                            <![endif]-->
                                            <div style="max-width:620px; margin:0 auto;" data-gjs-type="editable" class="gjs-editable main">
                                                {$content}
                                            </div>
                                            <!--[if mso]>
                                            </td></tr></table>
                                            <![endif]-->
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
            </body>
            </html>
        EOT;
    }
}
