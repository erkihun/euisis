<?php

declare(strict_types=1);

namespace App\Enums;

enum AuditEventType: string
{
    case EmployeeCreated = 'employee_created';
    case EmployeeUpdated = 'employee_updated';
    case AssignmentChanged = 'assignment_changed';
    case TransferRequested = 'transfer_requested';
    case TransferSubmitted = 'transfer_submitted';
    case TransferCurrentOrganizationConfirmed = 'transfer_current_organization_confirmed';
    case TransferReceivingOrganizationConfirmed = 'transfer_receiving_organization_confirmed';
    case TransferApproved = 'transfer_approved';
    case TransferRejected = 'transfer_rejected';
    case TransferCancelled = 'transfer_cancelled';
    case TransferCompleted = 'transfer_completed';
    case OrganizationCreated = 'organization_created';
    case OrganizationUpdated = 'organization_updated';
    case OrganizationLogoUploaded = 'organization_logo_uploaded';
    case OrganizationBrandingUpdated = 'organization_branding_updated';
    case HierarchyPublished = 'hierarchy_published';
    case CardRequested = 'card_requested';
    case CardVerified = 'card_verified';
    case CardApproved = 'card_approved';
    case CardRejected = 'card_rejected';
    case CardCancelled = 'card_cancelled';
    case CardPrinted = 'card_printed';
    case CardPrintedAnytime = 'card.printed_anytime';
    case CardExportedPng = 'card.exported_png';
    case CardIssued = 'card_issued';
    case CardActivated = 'card_activated';
    case CardLost = 'card_lost';
    case CardDamaged = 'card_damaged';
    case CardSuspended = 'card_suspended';
    case CardReplaced = 'card_replaced';
    case CardRevoked = 'card_revoked';
    case CardExpired = 'card_expired';
    case PrintBatchCreated = 'print_batch_created';
    case PrintBatchCompleted = 'print_batch_completed';
    case VerificationPerformed = 'verification_performed';
    case EntitlementGranted = 'entitlement_granted';
    case EntitlementRevoked = 'entitlement_revoked';
    case EntitlementOverridden = 'entitlement_overridden';
    case ServiceTransactionRecorded = 'service_transaction_recorded';
    case ServiceTransactionReversed = 'service_transaction_reversed';
    case ProviderApiDenied = 'provider_api_denied';
    case ExportPerformed = 'export_performed';
    case SecurityEvent = 'security_event';
    case ScopeChanged = 'scope_changed';
    case PermissionChanged = 'permission_changed';

    case UserCreated = 'user_created';
    case UserUpdated = 'user_updated';
    case UserPhotoUpdated = 'user_photo_updated';
    case UserDeactivated = 'user_deactivated';
    case UserRestored = 'user_restored';
    case ProfileUpdated = 'profile_updated';
    case ProfilePhotoUpdated = 'profile_photo_updated';
    case UserDeactivationBlockedSelf = 'user_deactivation_blocked_self';
    case UserDeactivationBlockedLastSuperAdmin = 'user_deactivation_blocked_last_super_admin';
    case UserLastSuperAdminRoleRemovalBlocked = 'user_last_super_admin_role_removal_blocked';
    case RoleCreated = 'role_created';
    case RoleUpdated = 'role_updated';
    case RoleDeleted = 'role_deleted';
    case OrganizationTypeCreated = 'organization_type_created';
    case OrganizationTypeUpdated = 'organization_type_updated';
    case OrganizationTypeArchived = 'organization_type_archived';
    case OrganizationTypeDeleted = 'organization_type_deleted';
    case OrganizationTypeRestored = 'organization_type_restored';
    case HierarchyVersionCreated = 'hierarchy_version_created';
    case HierarchyVersionUpdated = 'hierarchy_version_updated';
    case HierarchyVersionArchived = 'hierarchy_version_archived';
    case HierarchyRelationCreated = 'hierarchy_relation_created';
    case HierarchyRelationUpdated = 'hierarchy_relation_updated';
    case HierarchyRelationRemoved = 'hierarchy_relation_removed';
    case SettingUpdated = 'setting_updated';
    case PositionCreated = 'position_created';
    case PositionUpdated = 'position_updated';
    case PositionArchived = 'position_archived';
    case PositionRestored = 'position_restored';
    case ServiceTypeCreated = 'service_type_created';
    case ServiceTypeUpdated = 'service_type_updated';
    case ServiceTypeArchived = 'service_type_archived';
    case ServiceTypeRestored = 'service_type_restored';
    case EntitlementRuleCreated = 'entitlement_rule_created';
    case EntitlementRuleUpdated = 'entitlement_rule_updated';
    case EntitlementRuleArchived = 'entitlement_rule_archived';
    case EntitlementRuleRestored = 'entitlement_rule_restored';
    case SettingAssetUploaded = 'setting_asset_uploaded';
    case SettingsCacheCleared = 'settings_cache_cleared';
    case NotificationChannelTested = 'notification_channel_tested';
    case CodeRuleCreated = 'code_rule_created';
    case CodeRuleUpdated = 'code_rule_updated';
    case CodeRuleArchived = 'code_rule_archived';
    case CodeRuleRestored = 'code_rule_restored';
    case CodeGenerated = 'code_generated';
    case CodeManualOverrideUsed = 'code_manual_override_used';
    case CodeGenerationFailed = 'code_generation_failed';
    case CodeRuleSequenceReset = 'code_rule_sequence_reset';
    case RecordDeleted = 'record_deleted';
    case RecordRestored = 'record_restored';

    case OrganizationUnitCreated = 'organization_unit_created';
    case OrganizationUnitUpdated = 'organization_unit_updated';
    case OrganizationUnitArchived = 'organization_unit_archived';
    case OrganizationUnitRestored = 'organization_unit_restored';

    case OrganizationUnitTypeCreated = 'organization_unit_type_created';
    case OrganizationUnitTypeUpdated = 'organization_unit_type_updated';
    case OrganizationUnitTypeArchived = 'organization_unit_type_archived';
    case OrganizationUnitTypeRestored = 'organization_unit_type_restored';

    case UserOrganizationScopeAssigned = 'user.organization_scope.assigned';
    case UserOrganizationScopeUpdated = 'user.organization_scope.updated';
    case UserOrganizationScopeRemoved = 'user.organization_scope.removed';

    case PermissionCreated = 'permission.created';
    case PermissionUpdated = 'permission.updated';
    case PermissionDeleted = 'permission.deleted';
    case RolePermissionsUpdated = 'role.permissions_updated';
}
