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

---

# 重要错误记录 (Critical Bug Record)

## 错误：支付成功后未跳转到正确成功页面

### 问题描述
- **场景**：银行汇款管理员确认收款后、微信支付成功后，用户登录后点击查看订单详情
- **问题**：用户看到的是订单详情页（显示支付方式选择界面），而不是正确的成功页面
- **原因**：`order-details.php` 中的跳转逻辑只检查了 `is_onsite_pending`（现场缴费待付款），没有检查 `is_paid`（已支付状态）

### 修复方案
在 `templates/order-details.php` 第 29-33 行修改跳转逻辑：

```php
// 修改前（错误）：
if ( Conf_Utils::is_onsite_pending( $order_id ) ) {
    wp_redirect( Conf_Utils::get_success_url( $order_id ) );
    exit;
}

// 修改后（正确）：
if ( Conf_Utils::is_onsite_pending( $order_id ) || Conf_Utils::is_paid( $order_id ) ) {
    wp_redirect( Conf_Utils::get_success_url( $order_id ) );
    exit;
}
```

### 关键逻辑说明
| 状态 | 支付方式 | 行为 |
|------|---------|------|
| `unpaid` | `onsite` | 跳转成功页面 |
| `paid` | `bank` | 跳转成功页面 |
| `paid` | `wechat` | 跳转成功页面 |
| `pending` | `bank` | 显示订单详情页（银行信息+上传凭证） |
| `pending` | `wechat` | 显示订单详情页（微信支付按钮） |

### 相关文件
- `templates/order-details.php` - 跳转逻辑
- `templates/order-success.php` - 成功页面模板
- `includes/class-conf-utils.php` - 工具类（is_paid()、is_onsite_pending()）

### 日期
2026-03-27