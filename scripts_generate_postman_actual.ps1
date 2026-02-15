$ErrorActionPreference = 'Stop'

$moduleOrder = @('auth','shared','inventory','procurement','manufacturing','sales','maintenance','hr','compliance','integrations','reports')
$moduleLabel = @{
  auth='Auth'; shared='Shared'; inventory='Inventory'; procurement='Procurement'; manufacturing='Manufacturing'; sales='Sales'; maintenance='Maintenance'; hr='HR'; compliance='Compliance'; integrations='Integrations'; reports='Reports'
}

function Get-ModuleFromUri([string]$uri) {
  if ($uri -eq 'api/dashboard') { return 'shared' }
  $parts = $uri -split '/'
  if ($parts.Length -lt 2) { return $null }
  $m = $parts[1]
  if ($moduleOrder -contains $m) { return $m }
  return $null
}

function Expand-HttpMethods([string]$methodToken) {
  $methods = @()
  foreach ($m in ($methodToken -split '\|')) {
    if ($m -in @('HEAD','OPTIONS')) { continue }
    $methods += $m
  }
  return $methods
}

function Infer-Permission($middleware) {
  if (-not $middleware) { return '' }
  $p = @($middleware | Where-Object { $_ -like 'permission:*' } | ForEach-Object { $_ -replace '^permission:', '' })
  return ($p -join ',')
}

function Get-SampleBody([string]$uri, [string]$method) {
  if ($method -notin @('POST','PUT','PATCH')) { return $null }

  switch -Regex ($uri) {
    '^api/auth/login$' { return @{ email = '{{email}}'; password = '{{password}}' } }

    '^api/shared/approval-workflows$' { return @{ workflow_name='PO Approval'; document_type='PURCHASE_ORDER'; is_active=$true; steps=@(@{step_number=1}, @{step_number=2}) } }
    '^api/shared/approval-requests$' { return @{ entity_type='PURCHASE_ORDER'; entity_id='{{entity_id}}'; from_status='DRAFT'; to_status='APPROVED'; amount=12500 } }
    '^api/shared/approval-requests/\{.*\}/reject$' { return @{ reason='Missing mandatory supporting document' } }
    '^api/shared/number-series$' { return @{ entity_type='WORK_ORDER'; prefix='WO-'; suffix=''; format='{PREFIX}{DATE}-{NUMBER}'; padding=6; include_date=$true; date_format='ymd'; reset_on_date_change=$false } }
    '^api/shared/system-settings$' { return @{ setting_key='default_warehouse'; setting_value='{{warehouse_id}}'; setting_type='STRING'; module='inventory' } }

    '^api/inventory/items$' { return @{ item_code='RM-STEEL-001'; name='Mild Steel Sheet'; primary_uom_id='{{uom_id}}'; category_id='{{item_category_id}}'; item_type='STOCKABLE'; stock_type='RAW_MATERIAL'; is_batch_tracked=$false; is_serial_tracked=$false; standard_cost=12.5 } }
    '^api/inventory/stock-adjustments$' { return @{ warehouse_id='{{warehouse_id}}'; adjustment_type='PHYSICAL_COUNT'; reason='Cycle count variance'; lines=@(@{ item_id='{{item_id}}'; physical_quantity=95; system_quantity=100; unit_cost=12.5; notes='Counted during weekly stock check' }) } }
    '^api/inventory/warehouses-crud$' { return @{ name='Main Warehouse'; code='WH-MAIN'; type='WAREHOUSE'; address='Plant 1'; allow_negative_stock=$false; is_active=$true } }

    '^api/procurement/vendors$' { return @{ vendor_code='VND-001'; name='ABC Metals Pvt Ltd'; email='sales@abcmetals.test'; phone='+1-555-0100'; tax_id='TX-9988'; address='Industrial Zone'; is_active=$true } }
    '^api/procurement/purchase-orders$' { return @{ vendor_id='{{vendor_id}}'; order_date='2026-02-15'; expected_date='2026-02-20'; delivery_warehouse_id='{{warehouse_id}}'; currency='USD'; payment_terms='NET30'; notes='Urgent material'; lines=@(@{ line_number=1; item_id='{{item_id}}'; quantity=100; uom_id='{{uom_id}}'; unit_price=12.5; tax_rate=0; discount_percentage=0 }) } }
    '^api/procurement/grn$' { return @{ purchase_order_id='{{purchase_order_id}}'; warehouse_id='{{warehouse_id}}'; receipt_date='2026-02-15'; notes='Received in good condition'; lines=@(@{ po_line_id='{{purchase_order_line_id}}'; accepted_quantity=100; received_quantity=100; rejected_quantity=0; unit_price=12.5; quality_status='PENDING' }) } }
    '^api/procurement/purchase-invoices$' { return @{ invoice_number='PINV-2026-0001'; vendor_id='{{vendor_id}}'; purchase_order_id='{{purchase_order_id}}'; invoice_date='2026-02-15'; due_date='2026-03-17'; subtotal=1250; tax_amount=0; total_amount=1250; payment_status='UNPAID'; status='DRAFT'; lines=@(@{ line_number=1; item_id='{{item_id}}'; quantity=100; unit_price=12.5; tax_percentage=0; line_amount=1250 }) } }

    '^api/manufacturing/work-orders$' { return @{ item_id='{{finished_item_id}}'; bom_id='{{bom_id}}'; planned_quantity=50; scheduled_start_date='2026-02-16'; source_warehouse_id='{{source_warehouse_id}}'; target_warehouse_id='{{target_warehouse_id}}' } }
    '^api/manufacturing/work-orders/\{.*\}/issue-materials$' { return @{ materials=@(@{ work_order_material_id='{{work_order_material_id}}'; quantity=100; batch_id='{{batch_id}}' }) } }
    '^api/manufacturing/work-orders/\{.*\}/record-production$' { return @{ quantity=50; quantity_rejected=0; batch_number='FG-BATCH-001'; notes='Shift A production' } }
    '^api/manufacturing/production-plans$' { return @{ plan_number='PLAN-2026-001'; plan_date='2026-02-15'; planning_period_start='2026-02-16'; planning_period_end='2026-02-28'; status='DRAFT'; items=@(@{ item_id='{{finished_item_id}}'; planned_quantity=500; scheduled_start_date='2026-02-16'; scheduled_end_date='2026-02-20'; priority=5 }) } }
    '^api/manufacturing/boms-crud$' { return @{ item_id='{{finished_item_id}}'; bom_number='BOM-FG-001'; version=1; is_active=$true; base_quantity=1; uom_id='{{uom_id}}'; lines=@(@{ line_number=1; component_item_id='{{component_item_id}}'; quantity_per_unit=2; scrap_percentage=0 }) } }
    '^api/manufacturing/quality/inspections$' { return @{ inspection_number='QI-2026-001'; template_id='{{quality_template_id}}'; reference_type='GRN'; reference_id='{{grn_id}}'; item_id='{{item_id}}'; quantity_inspected=100; remarks='Initial QC' } }
    '^api/manufacturing/quality/inspections/\{.*\}/record-readings$' { return @{ readings=@(@{ parameter_id='{{quality_parameter_id}}'; reading_value='OK'; numeric_value=0; is_within_spec=$true; notes='Within tolerance' }) } }
    '^api/manufacturing/quality/inspections/\{.*\}/complete$' { return @{ status='PASSED'; overall_result='ACCEPTED'; remarks='Inspection passed' } }
    '^api/manufacturing/scrap$' { return @{ source_type='WORK_ORDER'; source_id='{{work_order_id}}'; item_id='{{item_id}}'; warehouse_id='{{warehouse_id}}'; scrap_quantity=5; scrap_value=0; scrap_reason='Process loss'; scrap_category='NORMAL' } }
    '^api/manufacturing/scrap/\{.*\}/dispose$' { return @{ disposal_method='DISPOSE'; disposed_quantity=5; disposal_date='2026-02-15' } }
    '^api/manufacturing/scrap/\{.*\}/recover$' { return @{ recovered_item_id='{{item_id}}'; recovered_quantity=1; recovery_value=10; recovery_date='2026-02-15'; sold_to='Scrap Buyer' } }

    '^api/sales/customers$' { return @{ customer_code='CUST-001'; name='Acme Distributors'; email='buyer@acme.test'; phone='+1-555-0110'; tax_id='GSTIN-123'; billing_address='Billing street'; shipping_address='Shipping street'; is_active=$true } }
    '^api/sales/orders$' { return @{ customer_id='{{customer_id}}'; order_date='2026-02-15'; expected_date='2026-02-18'; currency='USD'; notes='Priority order'; lines=@(@{ line_number=1; item_id='{{item_id}}'; quantity=30; uom_id='{{uom_id}}'; unit_price=25; tax_amount=0; line_amount=750 }) } }
    '^api/sales/delivery-notes$' { return @{ sales_order_id='{{sales_order_id}}'; warehouse_id='{{warehouse_id}}'; delivery_date='2026-02-16'; notes='Dispatch via truck'; lines=@(@{ line_number=1; sales_order_line_id='{{sales_order_line_id}}'; quantity=30; uom_id='{{uom_id}}'; batch_id='{{batch_id}}' }) } }
    '^api/sales/returns$' { return @{ return_number='SR-2026-001'; return_date='2026-02-20'; customer_id='{{customer_id}}'; warehouse_id='{{warehouse_id}}'; return_reason='Damage in transit'; return_type='DAMAGE'; status='PENDING'; lines=@(@{ line_number=1; item_id='{{item_id}}'; returned_quantity=5; accepted_quantity=0; rejected_quantity=0; disposition='RESTOCK' }) } }

    '^api/maintenance/machines$' { return @{ machine_code='MCH-001'; machine_name='CNC #1'; machine_type='CNC'; manufacturer='OEM'; model_number='CNC-X'; installation_date='2025-01-01'; maintenance_frequency_days=30; status='OPERATIONAL'; is_active=$true } }
    '^api/maintenance/preventive-schedules$' { return @{ schedule_code='PM-001'; machine_id='{{machine_id}}'; frequency_type='MONTHLY'; frequency_value=1; next_due_date='2026-03-01'; assigned_to='{{employee_id}}'; is_active=$true } }
    '^api/maintenance/preventive-tasks$' { return @{ task_number='PMT-001'; schedule_id='{{preventive_schedule_id}}'; machine_id='{{machine_id}}'; scheduled_date='2026-02-20'; status='SCHEDULED'; assigned_to='{{employee_id}}' } }
    '^api/maintenance/breakdown-reports$' { return @{ ticket_number='BD-2026-001'; machine_id='{{machine_id}}'; reported_at='2026-02-15T10:00:00Z'; problem_description='Unexpected stoppage'; severity='HIGH'; status='REPORTED' } }
    '^api/maintenance/breakdown-reports/\{.*\}/assign$' { return @{ assigned_to='{{employee_id}}' } }
    '^api/maintenance/breakdown-reports/\{.*\}/resolve$' { return @{ root_cause='Belt failure'; corrective_action='Replaced belt'; preventive_action='Weekly inspection'; downtime_minutes=120; resolved_at='2026-02-15T13:00:00Z' } }

    '^api/hr/employees$' { return @{ employee_code='EMP-001'; first_name='John'; last_name='Doe'; email='john.doe@test.com'; phone='+1-555-0101'; date_of_joining='2025-01-01'; department='Production'; designation='Operator'; employment_type='PERMANENT'; is_active=$true } }
    '^api/hr/shifts$' { return @{ shift_code='SHIFT-A'; shift_name='Morning'; start_time='08:00'; end_time='16:00'; break_duration_minutes=30; is_night_shift=$false; is_active=$true } }
    '^api/hr/attendance/clock-in$' { return @{ employee_id='{{employee_id}}'; attendance_date='2026-02-15'; shift_id='{{shift_id}}'; clock_in_time='2026-02-15T08:01:00Z' } }
    '^api/hr/attendance/clock-out$' { return @{ employee_id='{{employee_id}}'; attendance_date='2026-02-15'; clock_out_time='2026-02-15T16:05:00Z' } }

    '^api/compliance/documents$' { return @{ document_number='DOC-001'; document_name='SOP - Receiving'; document_type='SOP'; version='1.0'; effective_date='2026-02-15'; status='DRAFT'; file_path='docs/sop-receiving.pdf' } }
    '^api/compliance/certifications$' { return @{ certification_type='ISO_9001'; certification_number='ISO-9001-2026'; issuing_authority='ISO Body'; issue_date='2026-01-01'; expiry_date='2027-01-01'; status='ACTIVE'; scope='Manufacturing operations' } }

    '^api/integrations/accounting/exports$' { return @{ export_number='EXP-2026-001'; export_date='2026-02-15'; export_type='PURCHASE_INVOICE'; from_date='2026-02-01'; to_date='2026-02-15'; file_format='JSON'; status='PENDING' } }
    '^api/integrations/accounting/export-invoices$' { return @{ from_date='2026-02-01'; to_date='2026-02-15'; file_format='JSON' } }
    '^api/integrations/accounting/export-stock-valuation$' { return @{ from_date='2026-02-01'; to_date='2026-02-15'; file_format='CSV' } }
    '^api/integrations/barcode/generate$' { return @{ label_type='ITEM'; entity_type='ITEM'; entity_id='{{item_id}}'; barcode='ITEM-0001'; barcode_format='CODE128' } }
    '^api/integrations/barcode/scan$' { return @{ barcode='ITEM-0001' } }
    '^api/integrations/barcode/print-batch$' { return @{ label_ids=@('{{barcode_label_id}}') } }
    '^api/integrations/api-configurations$' { return @{ integration_name='QUICKBOOKS'; api_endpoint='https://api.example.com'; auth_type='API_KEY'; credentials=@{api_key='secret'}; is_active=$true; sync_frequency_minutes=60 } }
    '^api/integrations/weighbridge-readings$' { return @{ reading_number='WB-001'; reading_date='2026-02-15T09:00:00Z'; vehicle_number='TRK-100'; tare_weight=1200; gross_weight=4800; net_weight=3600; uom='KG'; reference_type='GRN'; reference_id='{{grn_id}}' } }

    '^api/reports/definitions$' { return @{ report_code='INV_STOCK_SUM'; report_name='Inventory Stock Summary'; report_category='INVENTORY'; sql_query='SELECT 1'; parameters=@{from_date='date'; warehouse_id='uuid'}; is_system_report=$false } }
    '^api/reports/custom/execute$' { return @{ report_code='INV_STOCK_SUM'; parameters=@{ from_date='2026-02-01'; to_date='2026-02-15'; warehouse_id='{{warehouse_id}}' } } }

    default {
      if ($method -in @('PUT','PATCH')) {
        return @{ notes = 'Updated via Postman' }
      }
      return $null
    }
  }
}

function New-ResponseExample([string]$name, [int]$code, [string]$body) {
  return [ordered]@{
    name = $name
    originalRequest = @{}
    status = if ($code -eq 200) { 'OK' } elseif ($code -eq 201) { 'Created' } elseif ($code -eq 401) { 'Unauthorized' } elseif ($code -eq 403) { 'Forbidden' } elseif ($code -eq 422) { 'Unprocessable Entity' } else { 'Error' }
    code = $code
    _postman_previewlanguage = 'json'
    header = @(@{ key = 'Content-Type'; value = 'application/json' })
    cookie = @()
    body = $body
  }
}

$routes = (php artisan route:list --path=api --json | ConvertFrom-Json)
$filtered = @($routes | Where-Object { $_.uri -ne 'api/{fallbackPlaceholder}' })

# Build endpoint summary rows
$summaryRows = @()

# Build folder structure
$folders = @{}
foreach ($m in $moduleOrder) {
  $folders[$m] = [ordered]@{ name = $moduleLabel[$m]; item = @() }
}

foreach ($route in $filtered) {
  $uri = [string]$route.uri
  $module = Get-ModuleFromUri $uri
  if (-not $module) { continue }

  $methods = Expand-HttpMethods ([string]$route.method)
  $permission = Infer-Permission $route.middleware
  $authRequired = $uri -ne 'api/auth/login'

  foreach ($method in $methods) {
    $name = "$method /$($uri -replace '^api/', '')"
    $urlPath = $uri -split '/'

    $request = [ordered]@{
      method = $method
      header = @()
      url = [ordered]@{
        raw = "{{base_url}}/$uri"
        host = @('{{base_url}}')
        path = $urlPath
      }
      description = "Controller: $($route.action)`nMiddleware: $([string]::Join(', ', @($route.middleware)))"
    }

    if ($authRequired) {
      $request.auth = [ordered]@{
        type = 'bearer'
        bearer = @(@{ key = 'token'; value = '{{access_token}}'; type = 'string' })
      }
    }

    $sampleBody = Get-SampleBody $uri $method
    if ($sampleBody -ne $null) {
      $request.header += @(@{ key = 'Content-Type'; value = 'application/json' })
      $request.body = [ordered]@{
        mode = 'raw'
        raw = ($sampleBody | ConvertTo-Json -Depth 20)
        options = [ordered]@{ raw = [ordered]@{ language = 'json' } }
      }
    }

    $successCode = if ($method -eq 'POST' -and ($uri -match '/(store|generate|export|clock-in|clock-out|complete|approve|submit|dispatch|release|record-production|issue-materials|recover|dispose)$' -or $uri -in @('api/auth/login'))) { 201 } else { 200 }
    if ($uri -eq 'api/auth/login') { $successCode = 200 }

    $successBodyObj = if ($uri -eq 'api/auth/login') {
      [ordered]@{
        success = $true
        message = 'Login successful'
        data = [ordered]@{
          access_token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.sample'
          token_type = 'Bearer'
          expires_in = 3600
          user = [ordered]@{
            id = '{{user_id}}'
            organization_id = '{{organization_id}}'
            email = '{{email}}'
            roles = @('super-admin')
          }
        }
        request_id = 'req-12345'
      }
    } else {
      [ordered]@{
        success = $true
        message = 'Request successful'
        data = @{}
        request_id = 'req-12345'
      }
    }

    $responses = @(
      (New-ResponseExample 'Success' $successCode (($successBodyObj | ConvertTo-Json -Depth 20))),
      (New-ResponseExample 'Validation Error Example' 422 (([ordered]@{ success=$false; message='Validation failed'; error_code='VALIDATION_ERROR'; request_id='req-12345'; errors=[ordered]@{ field=@('The field is required.') } } | ConvertTo-Json -Depth 20))),
      (New-ResponseExample 'Business Rule Error Example' 422 (([ordered]@{ success=$false; message='Business rule violation'; error_code='REQUEST_ERROR'; request_id='req-12345' } | ConvertTo-Json -Depth 20)))
    )

    $events = @()
    if ($uri -eq 'api/auth/login' -and $method -eq 'POST') {
      $events += [ordered]@{
        listen = 'test'
        script = [ordered]@{
          type = 'text/javascript'
          exec = @(
            'const json = pm.response.json();',
            'if (json?.data?.access_token) {',
            '  pm.environment.set("access_token", json.data.access_token);',
            '}'
          )
        }
      }
    }

    $item = [ordered]@{ name = $name; request = $request; response = $responses }
    if ($events.Count -gt 0) { $item.event = $events }

    $folders[$module].item += $item

    $summaryRows += [ordered]@{
      Module = $moduleLabel[$module]
      Method = $method
      Endpoint = "/$uri"
      Auth = if ($authRequired) { 'Bearer JWT' } else { 'None' }
      Permission = if ([string]::IsNullOrWhiteSpace($permission)) { '-' } else { $permission }
      Action = $route.action
    }
  }
}

$collectionItems = @()
foreach ($m in $moduleOrder) {
  $collectionItems += $folders[$m]
}

$errorFolder = [ordered]@{
  name = 'Error Examples'
  item = @(
    [ordered]@{
      name = 'Validation Error Payload Example'
      request = [ordered]@{
        method = 'GET'
        url = [ordered]@{ raw='{{base_url}}/api/inventory/items'; host=@('{{base_url}}'); path=@('api','inventory','items') }
        description = 'Reference example payload for validation failures (422).'
      }
      response = @(
        (New-ResponseExample '422 Validation Error' 422 (([ordered]@{ success=$false; message='Validation failed'; error_code='VALIDATION_ERROR'; request_id='req-12345'; errors=[ordered]@{ 'vendor_id'=@('The vendor id field is required.'); 'lines.0.quantity'=@('Quantity must be greater than 0.') } } | ConvertTo-Json -Depth 20)))
      )
    },
    [ordered]@{
      name = 'Business Rule Error Payload Example'
      request = [ordered]@{
        method = 'POST'
        url = [ordered]@{ raw='{{base_url}}/api/inventory/stock-adjustments/{{stock_adjustment_id}}/post'; host=@('{{base_url}}'); path=@('api','inventory','stock-adjustments','{{stock_adjustment_id}}','post') }
        description = 'Reference example payload for business rule failures (422).'
      }
      response = @(
        (New-ResponseExample '422 Business Rule Error' 422 (([ordered]@{ success=$false; message='Only APPROVED adjustments can be posted.'; error_code='REQUEST_ERROR'; request_id='req-12345' } | ConvertTo-Json -Depth 20)))
      )
    }
  )
}
$collectionItems += $errorFolder

$collection = [ordered]@{
  info = [ordered]@{
    name = 'SME ERP API - Actual Implemented Backend'
    _postman_id = [guid]::NewGuid().ToString()
    description = 'Generated from live Laravel route list. Includes only currently registered API endpoints.'
    schema = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
  }
  item = $collectionItems
  variable = @(
    @{ key='base_url'; value='http://localhost:8000'; type='string' },
    @{ key='access_token'; value=''; type='string' },
    @{ key='email'; value='admin@examp.com'; type='string' },
    @{ key='password'; value='password'; type='string' }
  )
}

$environment = [ordered]@{
  id = [guid]::NewGuid().ToString()
  name = 'SME ERP API - Local'
  values = @(
    @{ key='base_url'; value='http://localhost:8000'; type='default'; enabled=$true },
    @{ key='access_token'; value='eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.sample'; type='secret'; enabled=$true },
    @{ key='email'; value='admin@examp.com'; type='default'; enabled=$true },
    @{ key='password'; value='password'; type='secret'; enabled=$true },
    @{ key='organization_id'; value='00000000-0000-0000-0000-000000000001'; type='default'; enabled=$true },
    @{ key='user_id'; value='00000000-0000-0000-0000-000000000002'; type='default'; enabled=$true },
    @{ key='item_id'; value='00000000-0000-0000-0000-000000000101'; type='default'; enabled=$true },
    @{ key='finished_item_id'; value='00000000-0000-0000-0000-000000000102'; type='default'; enabled=$true },
    @{ key='component_item_id'; value='00000000-0000-0000-0000-000000000103'; type='default'; enabled=$true },
    @{ key='warehouse_id'; value='00000000-0000-0000-0000-000000000201'; type='default'; enabled=$true },
    @{ key='source_warehouse_id'; value='00000000-0000-0000-0000-000000000202'; type='default'; enabled=$true },
    @{ key='target_warehouse_id'; value='00000000-0000-0000-0000-000000000203'; type='default'; enabled=$true },
    @{ key='vendor_id'; value='00000000-0000-0000-0000-000000000301'; type='default'; enabled=$true },
    @{ key='customer_id'; value='00000000-0000-0000-0000-000000000302'; type='default'; enabled=$true },
    @{ key='uom_id'; value='00000000-0000-0000-0000-000000000401'; type='default'; enabled=$true },
    @{ key='item_category_id'; value='00000000-0000-0000-0000-000000000402'; type='default'; enabled=$true },
    @{ key='purchase_order_id'; value='00000000-0000-0000-0000-000000000501'; type='default'; enabled=$true },
    @{ key='purchase_order_line_id'; value='00000000-0000-0000-0000-000000000502'; type='default'; enabled=$true },
    @{ key='grn_id'; value='00000000-0000-0000-0000-000000000503'; type='default'; enabled=$true },
    @{ key='sales_order_id'; value='00000000-0000-0000-0000-000000000601'; type='default'; enabled=$true },
    @{ key='sales_order_line_id'; value='00000000-0000-0000-0000-000000000602'; type='default'; enabled=$true },
    @{ key='bom_id'; value='00000000-0000-0000-0000-000000000701'; type='default'; enabled=$true },
    @{ key='work_order_id'; value='00000000-0000-0000-0000-000000000702'; type='default'; enabled=$true },
    @{ key='work_order_material_id'; value='00000000-0000-0000-0000-000000000703'; type='default'; enabled=$true },
    @{ key='batch_id'; value='00000000-0000-0000-0000-000000000704'; type='default'; enabled=$true },
    @{ key='stock_adjustment_id'; value='00000000-0000-0000-0000-000000000705'; type='default'; enabled=$true },
    @{ key='machine_id'; value='00000000-0000-0000-0000-000000000801'; type='default'; enabled=$true },
    @{ key='preventive_schedule_id'; value='00000000-0000-0000-0000-000000000802'; type='default'; enabled=$true },
    @{ key='employee_id'; value='00000000-0000-0000-0000-000000000901'; type='default'; enabled=$true },
    @{ key='shift_id'; value='00000000-0000-0000-0000-000000000902'; type='default'; enabled=$true },
    @{ key='barcode_label_id'; value='00000000-0000-0000-0000-000000001001'; type='default'; enabled=$true },
    @{ key='quality_template_id'; value='00000000-0000-0000-0000-000000001101'; type='default'; enabled=$true },
    @{ key='quality_parameter_id'; value='00000000-0000-0000-0000-000000001102'; type='default'; enabled=$true },
    @{ key='entity_id'; value='00000000-0000-0000-0000-000000001201'; type='default'; enabled=$true }
  )
  _postman_variable_scope = 'environment'
  _postman_exported_at = (Get-Date).ToString('o')
  _postman_exported_using = 'Codex GPT-5'
}

$collectionPath = 'SME-ERP-API-Actual.postman_collection.json'
$envPath = 'SME-ERP-API-Actual.postman_environment.json'
$summaryPath = 'SME-ERP-API-Actual-endpoint-summary.md'

$collection | ConvertTo-Json -Depth 50 | Set-Content -Path $collectionPath -Encoding UTF8
$environment | ConvertTo-Json -Depth 20 | Set-Content -Path $envPath -Encoding UTF8

$summaryLines = @()
$summaryLines += '| Module | Method | Endpoint | Auth | Permission | Action |'
$summaryLines += '|---|---|---|---|---|---|'

$summaryRows = $summaryRows | Sort-Object Module, Endpoint, Method
foreach ($row in $summaryRows) {
  $summaryLines += "| $($row.Module) | $($row.Method) | `$($row.Endpoint)` | $($row.Auth) | $($row.Permission) | `$($row.Action)` |"
}
$summaryLines | Set-Content -Path $summaryPath -Encoding UTF8

Write-Output "Generated: $collectionPath"
Write-Output "Generated: $envPath"
Write-Output "Generated: $summaryPath"
Write-Output "Total endpoint-method entries: $($summaryRows.Count)"
