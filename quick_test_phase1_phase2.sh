#!/bin/bash

# Quick Test Script for Phase 1 & Phase 2
# Make sure your Laravel server is running: php artisan serve

BASE_URL="http://localhost:8000"
API_URL="${BASE_URL}/api/v1"

echo "=========================================="
echo "Phase 1 & Phase 2 Testing Script"
echo "=========================================="
echo ""

# Step 1: Get JWT Token for Product Owner
echo "Step 1: Getting JWT token for Product Owner..."
TOKEN=$(php get_token.php superadmin@productowner.com Admin123@ 2>/dev/null | grep -v "Token for" | grep -v "User ID" | grep -v "User Role" | grep -v "Is Product Owner" | head -1)

if [ -z "$TOKEN" ]; then
    echo "❌ Failed to get token. Please check credentials."
    exit 1
fi

echo "✓ Token obtained"
echo ""

# Step 2: Test Phase 1 - Block a user (use user ID 2, change if needed)
echo "Step 2: Testing Phase 1 - Block User (ID: 2)..."
BLOCK_RESPONSE=$(curl -s -X POST "${API_URL}/user-management/users/2/block" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost" \
  -d '{"reason": "Testing block functionality"}')

echo "Response: ${BLOCK_RESPONSE}"
echo ""

# Check if successful
if echo "$BLOCK_RESPONSE" | grep -q "blocked successfully"; then
    echo "✓ User blocked successfully"
else
    echo "⚠️  Block response: ${BLOCK_RESPONSE}"
fi

sleep 2

# Step 3: Test Phase 1 - Unblock the user
echo "Step 3: Testing Phase 1 - Unblock User (ID: 2)..."
UNBLOCK_RESPONSE=$(curl -s -X POST "${API_URL}/user-management/users/2/unblock" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Host: admincrm.localhost")

echo "Response: ${UNBLOCK_RESPONSE}"
echo ""

if echo "$UNBLOCK_RESPONSE" | grep -q "unblocked successfully"; then
    echo "✓ User unblocked successfully"
else
    echo "⚠️  Unblock response: ${UNBLOCK_RESPONSE}"
fi

sleep 2

# Step 4: Test Phase 2 - Access route with correct role
echo "Step 4: Testing Phase 2 - Access route as Super Admin..."
SUPER_ADMIN_RESPONSE=$(curl -s -X GET "${API_URL}/test/super-admin-only" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Host: admincrm.localhost")

echo "Response: ${SUPER_ADMIN_RESPONSE}"
echo ""

if echo "$SUPER_ADMIN_RESPONSE" | grep -q "Access granted"; then
    echo "✓ Super Admin route access granted"
else
    echo "⚠️  Super Admin route response: ${SUPER_ADMIN_RESPONSE}"
fi

sleep 2

# Step 5: Test Phase 2 - Access route with multiple roles
echo "Step 5: Testing Phase 2 - Access route with multiple allowed roles..."
MULTI_ROLE_RESPONSE=$(curl -s -X GET "${API_URL}/test/admin-or-super" \
  -H "Authorization: Bearer ${TOKEN}" \
  -H "Host: admincrm.localhost")

echo "Response: ${MULTI_ROLE_RESPONSE}"
echo ""

if echo "$MULTI_ROLE_RESPONSE" | grep -q "Access granted"; then
    echo "✓ Multi-role route access granted"
else
    echo "⚠️  Multi-role route response: ${MULTI_ROLE_RESPONSE}"
fi

echo ""
echo "=========================================="
echo "Testing Complete!"
echo "=========================================="
echo ""
echo "For more detailed testing, see: TESTING_GUIDE_PHASE1_PHASE2.md"
echo ""
echo "To get a token for any user:"
echo "  php get_token.php email@example.com password"
echo ""
echo "To setup roles:"
echo "  php setup_roles_for_testing.php"

