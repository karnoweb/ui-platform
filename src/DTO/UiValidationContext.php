<?php

declare(strict_types=1);

namespace KarnoWeb\TenantUi\DTO;

enum UiValidationContext
{
    /** Draft editing and preview use draft global theme. */
    case Draft;

    /** Page publish validates against the currently published global UI. */
    case Publish;
}
