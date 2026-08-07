# Righteous Fire of Arcane Devotion Hierophant

**Source:** Path of Building game data (`repos/path-of-building/`)  
**Playstyle:** Mana-stacking fire aura, walking simulator, spell DoT  
**Difficulty:** Medium

## Data-Driven Concept

Classic Righteous Fire scales from **life**. The transfigured gem `Righteous Fire of Arcane Devotion` explicitly scales from **mana** — PoB's `preDamageFunc` sets `FireDot = output.Mana * RFManaMultiplier`. This build stacks maximum mana and fire/burning modifiers, not life RF.

## Skill Data (PoB)

**Gem:** `Righteous Fire of Arcane Devotion`  
**Files:** `src/Data/Gems.lua` (variant `RighteousFireAltX`), `src/Data/Skills/act_int.lua` (`skills["RighteousFireAltX"]`)

| Property | Value (from data) |
|----------|-------------------|
| Tags | `Spell, AoE, Fire` |
| Requirements | 40 Str, 60 Int |
| Cast time | Instant (0) |
| Cooldown | 0.3s |
| Skill types | Spell, Buff, Area, Fire, Burning, DamageOverTime, **DegenOnlySpellDamage** |

**Constant stats (from `act_int.lua`):**

| Stat | Value |
|------|-------|
| Damage to nearby enemies | `4200% of max mana per minute` |
| Self-burn (non-lethal) | `5400% of max mana per minute` |
| Base AoE radius | 18 (quality adds +0.15 radius per %) |

**Key stat:** `spell_damage_modifiers_apply_to_skill_dot` — fire/burning/spell investment all apply to the aura damage.

**Mechanic (from description):** *"Engulfs you in magical fire... cast speed is substantially increased... ends when you have 1 life remaining. Requires mana."*

## Scaling Tags (PoB)

- `SkillType.DegenOnlySpellDamage` — scales as spell DoT, not attack
- `KeywordFlag.Fire`, `KeywordFlag.FireDot`
- `RFManaMultiplier` — damage tied to `output.Mana` in `preDamageFunc`
- Burning damage, fire DoT multiplier on gear

**Not life RF.** Do not path life-based RF nodes expecting them to scale this gem.

## Class & Ascendancy

**Templar → Hierophant** — mana, MoM, life-reserved auras via Prism Guardian.

Alternative: **Inquisitor** for consecrated ground + fire/burning multi.

## Core Skills

| Slot | Gem | Data rationale |
|------|-----|----------------|
| Body | Righteous Fire of Arcane Devotion | Main aura — instant cast buff |
| Links | Burning Damage → Efficacy → Elemental Focus → (Unbound Ailments / Lifetap) | Fire DoT supports |
| Auras (Prism Guardian) | Determination, Purity of Elements | Reserve on **life** — frees mana pool |
| Auras (Essence Worm) | Grace or Defiance Banner | Zero reservation in worm slot |
| Utility | Flame Dash, Molten Shell | Movement + buffer at 1 life |

## Uniques from PoB Data

| Item | File | Relevant mods |
|------|------|---------------|
| **Cloak of Defiance** | `Uniques/body.lua` | `+(100–150) maximum Mana`, `Regenerate 1% of Mana per second`, grants **Mind Over Matter** |
| **Prism Guardian** | `Uniques/shield.lua` | `Socketed Gems Cost and Reserve Life instead of Mana`, `+2 to Level of Socketed Aura Gems`, `30% increased Reservation Efficiency` |
| **Essence Worm** | `Uniques/ring.lua` | `Socketed Gems have no Reservation` — free defensive aura |
| **Ashcaller** | `Uniques/wand.lua` | `+(15–25)% to Fire Damage over Time Multiplier`, burning damage, ignite (variant 2) |
| **The Brass Dome** | `Uniques/body.lua` | `Take no Extra Damage from Critical Strikes` — RF sits at 1 life |

## Rare Gear Priorities

- Rings/amulet: maximum mana, fire DoT multi, regeneration
- Belt: life + mana hybrid, flask slots
- Jewels: mana %, burning damage, fire DoT multi

## Passive Tree

- **Keystones:** Mind Over Matter (or Cloak of Defiance), Eldritch Battery optional
- Mana %, mana regen, intelligence
- Fire damage, burning damage, fire DoT multiplier wheels
- Life nodes for MoM buffer (not for RF damage)
- **Cluster jewels** (`ClusterJewels.lua`): medium **Fire Damage** — notables **Burning Bright**, **Wrapped in Flame**, **Fan the Flames**

## Pantheons (from `Pantheons.lua`)

- **Major:** Soul of Arakaali — `10% reduced Damage taken from Damage Over Time` (mitigates self-burn)
- **Minor:** Soul of Abberath — `60% less Duration of Ignite on You`

## Defences

- RF ends at 1 life — use MoM + large mana pool so hits go to mana first
- Life-reserved auras on Prism Guardian keep armour/evasion without touching mana
- Brass Dome removes crit vulnerability at 1 life
- Self-burn is `nonlethal` per stat name — managed via Arakaali + mana regen

## PoB Import Tip

In Path of Building, check that RF of Arcane Devotion shows DPS scaling when you add mana on gear. Toggle the buff on. Compare to classic RF — the mana-based `preDamageFunc` is the differentiator.

## Data File Index

```
src/Data/Gems.lua              → RighteousFireAltX gem definition
src/Data/Skills/act_int.lua    → mana scaling, preDamageFunc, burning stats
src/Data/Uniques/body.lua      → Cloak of Defiance, Brass Dome
src/Data/Uniques/shield.lua    → Prism Guardian
src/Data/Uniques/wand.lua      → Ashcaller
src/Data/ClusterJewels.lua     → Fire/burning cluster notables
src/Modules/CalcPerform.lua    → DoT perform pass
```
