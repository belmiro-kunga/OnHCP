import { computed } from 'vue'
import { useAuth } from './useAuth'

// Centralized RBAC/ABAC helper for the frontend
// RBAC: static role->permissions mapping
// ABAC: simple attribute-based predicates, can be extended per permission

const ROLE_PERMISSIONS = {
  admin: ['*'],
  manager: [
    'admin.dashboard.view',
    'users.view',
    'users.manage', // manage within scope via ABAC
    'roles.view',
    'roles.manage',
    'departments.view',
    'departments.manage',
    'audit.view',
    'audit.export',
    'security.view',
  ],
  collaborator: [
    'admin.dashboard.view',
    'users.view',
    'roles.view',
    'departments.view',
    'audit.view',
  ],
  guest: [
    'admin.dashboard.view',
  ],
}

function matchRbac(roles, permission) {
  if (!roles || roles.length === 0) return false
  // admin or wildcard
  if (roles.includes('admin')) return true
  return roles.some((role) => {
    const allowed = ROLE_PERMISSIONS[role] || []
    return allowed.includes('*') || allowed.includes(permission)
  })
}

// Basic ABAC examples; extend with real business rules
// context may contain: resource attributes, org scopes, actions
function matchAbac(userAttrs, permission, context = {}) {
  // Admin bypass via RBAC already handled

  // Example: managers can manage users only in their department/branch
  if (permission === 'users.manage') {
    if (!context?.targetUser) return false
    const { department: ud, branch: ub } = userAttrs || {}
    const { department: td, branch: tb } = context.targetUser?.attributes || {}
    if (!ud || !ub || !td || !tb) return false
    return ud === td && ub === tb
  }

  // Example: collaborators can view users in same department
  if (permission === 'users.view') {
    if (!context?.targetUser) return true // default to list view allowed by RBAC
    const { department: ud } = userAttrs || {}
    const { department: td } = context.targetUser?.attributes || {}
    if (!ud || !td) return false
    return ud === td
  }

  // Roles management: managers can manage roles within their scope
  if (permission === 'roles.manage') {
    if (!context?.targetRole) return true // default to list/create allowed by RBAC
    const { clearance: uc } = userAttrs || {}
    const { level: rl } = context.targetRole || {}
    if (!uc || !rl) return false
    return uc >= rl // can only manage roles at or below their clearance level
  }

  // Departments management: managers can manage departments within their branch
  if (permission === 'departments.manage') {
    if (!context?.targetDepartment) return true // default to list/create allowed by RBAC
    const { branch: ub } = userAttrs || {}
    const { branch: db } = context.targetDepartment || {}
    if (!ub || !db) return false
    return ub === db
  }

  // Default: no ABAC restriction
  return true
}

export function usePermissions() {
  const { roles, attributes, hasRole, currentUser } = useAuth()

  const can = (permission, context = {}) => {
    // Admin bypass
    if (hasRole('admin')) return true
    // Backend explicit permissions (if provided)
    const explicit = currentUser.value?.permissions
    if (Array.isArray(explicit) && explicit.includes(permission)) return true
    // RBAC
    const rbac = matchRbac(roles.value, permission)
    if (!rbac) return false
    // ABAC for granular permissions
    return matchAbac(attributes.value, permission, context)
  }

  const canAny = (perms, context = {}) => perms.some((p) => can(p, context))
  const canAll = (perms, context = {}) => perms.every((p) => can(p, context))

  return {
    roles,
    attributes,
    hasRole,
    can,
    canAny,
    canAll,
  }
}
