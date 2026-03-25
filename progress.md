# 银行汇款流程修改进展 (Progress Report)

## 修改目标
将原本在“提交订单”时一并上传银行汇款凭证的流程，解耦拆分为以下步骤，以贴合原型图 (04-02 和 05) 的逻辑：
1. 提交订单并锁定名额（此时不再强制要求上传凭证）。
2. 跳转到专用的汇款详情页，展示订单金额、订单号及银行账户信息，并提供上传凭证的入口。
3. 用户上传凭证并提交。
4. 跳转到“等待财务确认”的成功页面。

## 已完成的修改

### 1. 前端模板修改 (`templates/registration-form.php`)
- **调整 Step 3 (Review & Pay)**：当用户选择“Bank Transfer”时，隐藏了原本复杂的银行账户信息和上传凭证框，仅保留一条提示信息：“确认订单后，您将在下一步查看汇款账户并上传凭证”。
- **新增 Step 4 (Bank Transfer - `step-bank-transfer`)**：
  - 动态显示需汇款的金额和生成的订单号。
  - 显示后台配置的银行账户信息。
  - 提供汇款凭证图片上传区域。
- **新增 Step 5 (Bank Success - `step-bank-success`)**：
  - 上传成功后显示的界面，提示用户凭证已收到，等待核实。
- **原成功页顺延为 Step 6 (`step-success`)**：用于其他支付方式（如微信支付和现场缴费）的直接成功展示。

### 2. 前端交互逻辑修改 (`assets/js/registration.js`)
- **表单提交逻辑解耦**：
  - 当 `paymentMethod === 'bank'` 时，在订单提交成功后，不再渲染统用的成功页面。
  - 将总金额和订单号赋值给新的 `step-bank-transfer` 页面元素，并切换到该步骤。
  - 将生成的 `orderId` 绑定到凭证提交按钮的 `dataset` 上。
- **新增凭证上传交互**：
  - 绑定了 `#btn-submit-receipt` 的点击事件。
  - 构造 `FormData`，包含 `order_id` 和用户选择的文件 `bank_receipt`。
  - 通过 AJAX 调用后端的 `conf_upload_bank_receipt` 接口。
  - 成功上传后，切换显示 `step-bank-success` 等待确认页面。

### 3. 后端接口修改 (`includes/class-conf-registration.php`)
- **新增 AJAX Handler (`handle_upload_bank_receipt`)**：
  - 注册了 `wp_ajax_conf_upload_bank_receipt` action。
  - 实现了文件接收与验证逻辑（Nonce 验证、用户登录状态验证、订单有效性与归属验证）。
  - 使用 WordPress 核心的 `wp_handle_upload` 安全地保存图片。
  - 将上传成功的图片 URL 更新为订单的 `conf_bank_receipt_url` 字段。
  - 返回 JSON 格式的成功与否状态供前端解析。

## 后续建议
- 目前已将订单与凭证上传分离，如果用户在 Step 4 刷新了页面导致未上传凭证，可以考虑在“用户中心”或“订单详情页”保留一个补传凭证的入口（当前系统如果在 `templates/order-details.php` 已有补传逻辑则不受影响）。