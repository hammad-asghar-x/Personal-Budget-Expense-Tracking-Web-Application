#!/bin/bash
# ============================================
# PHP Security Checker (SAST)
# Static Analysis Security Testing Script
# Target: Laravel 12 Budget Tracker
# Date: 2025-06-18
# ============================================

echo "=========================================="
echo "🔐 PHP Security Checker - SAST Scan"
echo "Target: Personal Budget & Expense Tracker"
echo "Date: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# Check for hardcoded secrets
echo ""
echo "[1/5] Scanning for hardcoded secrets..."
grep -rn --include="*.php" \
  -E "(password|secret|api_key|apikey|token)\s*=\s*['\"][^'\"]+['\"]" \
  --exclude-dir=vendor --exclude-dir=node_modules app/ config/ \
  | grep -v ".env" | grep -v "factory" || echo "  ✅ No hardcoded secrets found"

# Check for SQL injection risks
echo ""
echo "[2/5] Scanning for SQL injection risks..."
grep -rn --include="*.php" \
  -E "(DB::raw|whereRaw|selectRaw|orderByRaw)\s*\(" \
  --exclude-dir=vendor app/ || echo "  ✅ No raw SQL queries detected"

# Check for XSS risks
echo ""
echo "[3/5] Scanning for XSS vulnerabilities..."
grep -rn --include="*.blade.php" \
  -E "\{!!\s" \
  --exclude-dir=vendor resources/views/ || echo "  ✅ No unescaped output detected"

# Check for CSRF protection
echo ""
echo "[4/5] Verifying CSRF middleware..."
grep -rn "VerifyCsrfToken" --exclude-dir=vendor app/Http/Kernel.php \
  && echo "  ✅ CSRF middleware is active" || echo "  ⚠️ CSRF middleware not found"

# Check dependency vulnerabilities
echo ""
echo "[5/5] Checking composer dependencies for known CVEs..."
if command -v composer &> /dev/null; then
  composer audit --format=table 2>/dev/null || echo "  ℹ️ Run 'composer audit' manually"
else
  echo "  ⚠️ Composer not found. Skipping dependency audit."
fi

echo ""
echo "=========================================="
echo "✅ SAST Scan Complete"
echo "=========================================="