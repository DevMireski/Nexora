export function getTokenPayload() {
    const token = localStorage.getItem('nexora_token')
    if (!token) return null
    try {
        // JWT payload is base64url encoded
        const base64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/')
        return JSON.parse(atob(base64))
    } catch {
        return null
    }
}

export function isAdmin() {
    const payload = getTokenPayload()
    return Array.isArray(payload?.roles) && payload.roles.includes('admin')
}
