# Cafeteria Settings Integration

The scan calendar reflects Cafeteria Settings:

- weekly day rules from `cafeteria_day_rules`
- public holidays from `public_holidays`
- special open/closed/no-subsidy days from `cafeteria_special_days`
- employee leave/exclusion periods from `employee_cafeteria_exclusions`
- consumed days from `cafeteria_transaction_consumed_days`

The default usage mode may only be `single_day` or `use_remaining_week`. Custom Amount is intentionally removed from settings and scan processing because subsidy is fixed by configured subsidy rules.

Provider users see only settings-driven scan and calendar data for providers assigned to them unless they hold explicit all-provider permissions.
