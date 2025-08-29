import{C as X,G as Q,_ as Z}from"./CopyFrom-DNWS41q7.js";import{_ as E}from"./Modal.vue_vue_type_style_index_0_scoped_fd08cd24_lang-B7C873nC.js";import{T as K}from"./TableContainer-CTCvRGFw.js";import{_ as ee}from"./Main-mJnHo3lV.js";import{S as te}from"./Save-BpuKOSbe.js";import{B as se}from"./Back-CaeIMo8B.js";import{C as oe}from"./Cart-B2OqdkTV.js";import{_ as ne}from"./_plugin-vue_export-helper-DlAUqK2U.js";import{d as p,G as R,k as ae,S as ie,c as le,w as b,o as v,b as c,e as g,a as d,f as x,t as _,n as D,F as re,h as ue,g as $,u as de,C as ce}from"./app-DJ_ZTFbI.js";import{P as M,D as pe}from"./dataTables-Dqt6QQHC.js";import"./Modal-CKYSH-Ul.js";import"./InputError-PkxbjC5q.js";import"./Logout-CqZ_wKbj.js";/* empty css                                                             */import"./PartyCake143-CS_JfOvx.js";const me={key:0,class:"fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm"},fe={class:"mb-4 flex justify-between items-center px-10"},be={class:"flex space-x-2"},ve={class:"relative column-visibility-dropdown"},ge={key:0,class:"absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"},ye={class:"py-1",role:"menu"},Te=["checked","onChange"],he={class:"flex space-x-2"},Ee={class:"inline-flex items-center"},we=["checked","onChange"],Ce={name:"StockCountingLine",inheritAttrs:!1,mounted(){this.initializeTooltips(),this.setupKeyboardShortcuts()},methods:{initializeTooltips(){document.querySelectorAll("[data-tooltip]").forEach(w=>{})},setupKeyboardShortcuts(){window.addEventListener("keydown",r=>{(r.ctrlKey||r.metaKey)&&r.key==="s"&&(r.preventDefault(),this.isActionDisabled||this.updateAllCountedValues())})},toggleRowSelection(r){this.$emit("row-selection-change",r)}},beforeUnmount(){window.removeEventListener("keydown",this.setupKeyboardShortcuts)}},Ae=Object.assign(Ce,{props:{stockcountingtrans:{type:Array,required:!0},journalid:{type:[String,Number],required:!0},items:{type:Array,required:!0},isPosted:{type:Number,required:!0}},setup(r){M.use(pe);const w=r,y=p(""),C=p(!1),N=p(!1),A=p(!1),S=p(!1),k=p([]),m=R({}),i=R({text:"",type:""}),T=p(!1),l=p({ITEMID:!0,itemname:!0,itemgroup:!0,ADJUSTMENT:!0,RECEIVEDCOUNT:!0,VARIANCE:!0,TRANSFERCOUNT:!0,WASTECOUNT:!0,WASTETYPE:!0,COUNTED:!0}),f=p(null),h=p([{data:"ITEMID",title:"ITEMID",width:"120px",visible:l.value.ITEMID},{data:"itemname",title:"ITEMNAME",width:"200px",visible:l.value.itemname},{data:"itemgroup",title:"CATEGORY",width:"150px",visible:l.value.itemgroup},{data:"ADJUSTMENT",title:"ORDER",width:"100px",visible:l.value.ADJUSTMENT,render:function(e,s,t){return s==="display"?`
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${Number(e).toFixed(0)}"
                        min="0"
                        data-field="ADJUSTMENT"
                        disabled
                        style="opacity: 0.6;"
                    >
                `:e}},{data:"RECEIVEDCOUNT",title:"ACTUAL RECEIVED",width:"120px",visible:l.value.RECEIVEDCOUNT,render:function(e,s,t){if(s==="display"){const o=Number(e),n=new Date,a=n.getHours(),u=t.TRANSDATE===n.toISOString().split("T")[0],O=a>=12||a===0,U=t.posted===1||!u||O;return`
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${o.toFixed(0)}"
                        min="0"
                        data-field="RECEIVEDCOUNT"
                        ${U?"disabled":""}
                        style="${U?"opacity: 0.6;":""}"
                        title="${O?"Receiving is disabled between 12 PM and 12 AM":""}"
                    >
                `}return e}},{data:null,title:"VARIANCE",width:"100px",render:function(e,s,t){if(s==="display"){const o=Number(t.ADJUSTMENT),n=Number(t.RECEIVEDCOUNT),a=o-n;return`
                    <div class="text-center p-2" 
                         style="background-color: ${a===0?"#f3f3f3":a<0?"#ffebee":"#e8f5e9"}">
                        ${a}
                    </div>
                `}return""}},{data:"TRANSFERCOUNT",title:"TRANSFER",width:"120px",visible:l.value.TRANSFERCOUNT,render:function(e,s,t){if(s==="display"){const o=Number(e),n=new Date().toISOString().split("T")[0],a=t.TRANSDATE===n,u=t.posted===1||!a;return`
                <input type="number" 
                    class="counted-input form-input w-full rounded-md"
                    value="${o.toFixed(0)}"
                    min="0"
                    data-field="TRANSFERCOUNT"
                    ${u?"disabled":""}
                    style="${u?"opacity: 0.6;":""}"
                >
            `}return e}},{data:"WASTECOUNT",title:"WASTE COUNT",width:"120px",visible:l.value.WASTECOUNT,render:function(e,s,t){if(s==="display"){const o=Number(e),n=t.posted===1||t.WASTETYPE!==null;return`
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${o.toFixed(0)}"
                        min="0"
                        data-field="WASTECOUNT"
                        ${n?"disabled":""}
                        style="${n?"opacity: 0.6;":""}"
                    >
                `}return e}},{data:"WASTETYPE",title:"WASTE TYPE",width:"150px",visible:l.value.WASTETYPE,render:function(e,s,t){if(s==="display"){const o=t.posted===1||e!==null,n=["throw_away","early_molds","pull_out","rat_bites","ant_bites"],a=e||"";return`
                    <select 
                        class="waste-type-select form-select w-full rounded-md"
                        data-field="WASTETYPE"
                        ${o?"disabled":""}>
                        <option value="">Select type</option>
                        ${n.map(u=>`
                            <option value="${u}" ${a===u?"selected":""}>
                                ${u.replace(/_/g," ")}
                            </option>
                        `).join("")}
                    </select>
                `}return e}},{data:"COUNTED",title:"ACTUAL COUNT",width:"120px",visible:l.value.COUNTED,render:function(e,s,t){if(s==="display"){const o=Number(e),n=t.TRANSDATE===new Date().toISOString().split("T")[0],a=t.posted===1||!n;return`
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${o.toFixed(0)}"
                        min="0"
                        data-field="COUNTED"
                        ${a?"disabled":""}
                        style="${a?"opacity: 0.6;":""}"
                    >
                `}return e}}]),V={paging:!1,scrollX:!0,scrollY:"70vh",scrollCollapse:!0,responsive:!0,processing:!0,stateSave:!0,columns:h.value,language:{processing:"Loading..."},initComplete:function(e,s){f.value&&(f.value.dtInstance=this.api())},drawCallback:function(e){this.api().rows().every(function(){const t=this.data();this.node().querySelectorAll(".counted-input, .waste-type-select").forEach(a=>{t.posted||a.addEventListener("change",u=>j(u,t))})})}},L=e=>{l.value[e]=!l.value[e];const s=h.value.find(t=>t.data===e);if(s&&(s.visible=l.value[e]),f.value)try{const t=f.value.dt;if(t){const o=h.value.findIndex(n=>n.data===e);o!==-1&&t.column(o).visible(l.value[e])}}catch(t){console.error("Error updating column visibility:",t)}},W=()=>{T.value=!T.value},I=e=>{const s=document.querySelector(".column-visibility-dropdown");s&&!s.contains(e.target)&&(T.value=!1)},j=(e,s)=>{const t=e.target.dataset.field;if(!t||s.posted)return;m[s.ITEMID]||(m[s.ITEMID]={});const o=e.target.type==="number"?parseFloat(e.target.value)||0:e.target.value;if(m[s.ITEMID][t]=o,e.target.type==="number"){const n=o===0?"#f3f3f3":"white";e.target.style.backgroundColor=n}},F=async()=>{var e,s;try{if(Object.keys(m).length===0){i.text="No changes to update",i.type="info";return}C.value=!0,i.text="Updating values...",i.type="info";const t=await ce.post("/api/stock-counting-line/update-all-counted-values",{journalId:w.journalid,updatedValues:m});t.data.success&&(i.text=t.data.message,i.type="success",Object.entries(m).forEach(([o,n])=>{const a=k.value.find(u=>u.ITEMID===o);a&&Object.assign(a,n)}),Object.keys(m).forEach(o=>delete m[o]),setTimeout(()=>window.location.reload(),1e3))}catch(t){console.error("Update error:",t),i.text=((s=(e=t.response)==null?void 0:e.data)==null?void 0:s.message)||"Update failed",i.type="error"}finally{C.value=!1,setTimeout(()=>{i.text="",i.type=""},3e3)}},B=()=>{window.location.href="/StockCounting"},J=e=>{window.location.href=`/ViewStockCountingLine/${e}`},G=e=>{y.value=e,A.value=!0},P=()=>{S.value=!1},Y=()=>{A.value=!1},q=()=>{N.value=!1},H=e=>{console.log("Selected Item:",e)},z=()=>{if(f.value&&f.value.dt){const e=f.value.dt;window.dataTableInstance=e}};return ae(()=>{k.value=w.stockcountingtrans,document.addEventListener("click",I),setTimeout(z,0)}),ie(()=>{document.removeEventListener("click",I)}),(e,s)=>(v(),le(ee,{"active-tab":"STOCK"},{modals:b(()=>[c(X,{"show-modal":S.value,JOURNALID:y.value,items:r.items,onToggleActive:P,onSelectItem:H},null,8,["show-modal","JOURNALID","items"]),c(Q,{"show-modal":A.value,JOURNALID:y.value,onToggleActive:Y},null,8,["show-modal","JOURNALID"]),c(Z,{"show-modal":N.value,JOURNALID:y.value,onToggleActive:q},null,8,["show-modal","JOURNALID"])]),main:b(()=>[c(K,null,{default:b(()=>[C.value?(v(),g("div",me,s[2]||(s[2]=[d("div",{class:"text-white text-2xl"},"Loading...",-1)]))):x("",!0),i.text?(v(),g("div",{key:1,class:D(["p-4 mb-4 rounded-md",i.type==="success"?"bg-green-100 text-green-700":i.type==="error"?"bg-red-100 text-red-700":"bg-blue-100 text-blue-700"])},_(i.text),3)):x("",!0),d("div",fe,[d("div",be,[d("div",ve,[d("button",{onClick:W,class:"bg-navy px-4 py-2 rounded-md text-white flex items-center space-x-2"},s[3]||(s[3]=[d("span",null,"Show/Hide Columns",-1),d("svg",{class:"w-4 h-4",fill:"none",stroke:"currentColor",viewBox:"0 0 24 24"},[d("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M19 9l-7 7-7-7"})],-1)])),T.value?(v(),g("div",ge,[d("div",ye,[(v(!0),g(re,null,ue(l.value,(t,o)=>(v(),g("label",{key:o,class:"flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"},[d("input",{type:"checkbox",checked:t,onChange:n=>L(o),class:"mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"},null,40,Te),$(" "+_(o),1)]))),128))])])):x("",!0)]),c(E,{onClick:B,class:"bg-navy px-4 py-2"},{default:b(()=>[c(se,{class:"h-5 w-5"})]),_:1}),c(E,{onClick:s[0]||(s[0]=t=>G(r.journalid)),class:D({"bg-navy px-4 py-2":!e.isActionDisabled,"bg-gray-400 cursor-not-allowed px-4 py-2":e.isActionDisabled}),disabled:e.isActionDisabled},{default:b(()=>s[4]||(s[4]=[$(" GENERATE ")])),_:1},8,["class","disabled"]),c(E,{onClick:s[1]||(s[1]=t=>J(r.journalid)),class:"bg-navy px-4 py-2"},{default:b(()=>[c(oe,{class:"h-5 w-5"})]),_:1})]),d("div",he,[c(E,{onClick:F,class:D({"bg-navy px-4 py-2":!e.isActionDisabled,"bg-gray-400 cursor-not-allowed px-4 py-2":e.isActionDisabled}),disabled:e.isActionDisabled},{default:b(()=>[c(te,{class:"h-5 w-5"})]),_:1},8,["class","disabled"])])]),c(de(M),{ref_key:"dataTableRef",ref:f,data:r.stockcountingtrans,columns:h.value,class:"w-full display nowrap",options:V},{action:b(t=>[d("label",Ee,[d("input",{type:"checkbox",class:"form-checkbox h-5 w-5 text-blue-600 rounded",checked:t.selected,onChange:o=>e.toggleRowSelection(t)},null,40,we)])]),_:1},8,["data","columns"])]),_:1})]),_:1}))}}),je=ne(Ae,[["__scopeId","data-v-57aa1bce"]]);export{je as default};
