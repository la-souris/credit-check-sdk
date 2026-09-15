<?php

declare(strict_types=1);

namespace LaSouris\CreditCheck\Sdk\CreditCheck\Applicant;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}
