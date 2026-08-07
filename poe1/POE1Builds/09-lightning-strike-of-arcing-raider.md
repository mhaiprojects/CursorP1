# Lightning Strike of Arcing Raider

**Source:** Path of Building game data (`repos/path-of-building/`)  
**Playstyle:** Melee + chaining lightning projectiles, shock explosions  
**Difficulty:** Medium

## Data-Driven Concept

`Lightning Strike of Arcing` is a transfigured attack gem with two damage parts (melee + projectiles) and per-chain damage scaling. PoB data defines weapon types, conversion, and a penalty on projectile ailment damage — informing support and unique choices.

## Skill Data (PoB)

**Gem:** `Lightning Strike of Arcing`  
**Files:** `src/Data/Gems.lua` (variant `LightningStrikeAltX`), `src/Data/Skills/act_dex.lua` (`skills["LightningStrikeAltX"]`)

| Property | Value (from data) |
|----------|-------------------|
| Tags | `Attack, Projectile, Melee, Strike, Lightning, Chaining` |
| Requirements | 60 Dex, 40 Int |
| Cast time | 1.0 (attack speed scales) |
| Skill types | Attack, RangedAttack, Projectile, Melee, Lightning, **Chains**, Multistrikeable |

**Constant stats:**

| Stat | Value |
|------|-------|
| Phys → lightning conversion | 50% |
| Damage per chain | **+10% more** (`skill_damage_+%_final_per_chain_from_skill_specific_stat`) |
| Projectile ailment damage | **-50% more** on hit ailments (`active_skill_hit_ailment_damage_with_projectile_+%_final`) |
| Quality | +0.1 chains per quality % |

**Damage parts** (from `parts` table):

1. **Melee hit** — `melee = true`, `projectile = false`
2. **Projectiles** — `melee = false`, `projectile = true` (chains between enemies)

**Weapon types allowed:** Claw, Dagger, Axe, Mace, Sword, Sceptre, Staff (`act_dex.lua` lines 10780–10791).

## Scaling Tags (PoB)

- Attack + lightning hit damage (prioritize over ailments on projectile part)
- Attack speed, crit chance, crit multi
- Chain count (quality + tree + gear)
- Shock effect (`Shocked Enemies you Kill Explode` synergy from uniques)
- **Avoid** building around projectile ignite/shock ailment DPS — data imposes -50% ailment on projectile part

## Class & Ascendancy

**Ranger → Raider** — onslaught, phasing, elemental damage — matches `dexterity` gem tag.

Alternative: **Shadow → Assassin** for crit + poison immunity, or **Inquisitor** for ele pen.

## Core Skills

| Slot | Gem | Data rationale |
|------|-----|----------------|
| Body 5–6L | Lightning Strike of Arcing → Trinity → Elemental Damage with Attacks → Inspiration → (Melee Phys / Impale) | Attack + lightning; Trinity for tri-element |
| Movement | Whirling Blades or Leap Slam | Dex requirement |
| Auras | Hatred, Wrath, Determination | Lightning + phys damage |
| Utility | Blood Rage, Mark (Assassin's Mark) | Frenzy + crit |

## Uniques from PoB Data

| Item | File | Relevant mods |
|------|------|---------------|
| **Inpulsa's Broken Heart** | `Uniques/body.lua` | `(20–50)% increased Damage if you have Shocked an Enemy Recently`, `Shocked Enemies you Kill Explode` (5% life as lightning) |
| **Terminus Est** | `Uniques/sword.lua` | `20% increased Attack Speed`, `(50–75)% increased Critical Strike Chance`, `Gain a Frenzy Charge on Critical Strike` |
| **Berek's Respite** | `Uniques/ring.lua` | `When you Kill a Shocked Enemy, inflict an equivalent Shock on each nearby Enemy` — propagates shocks for chain clears |
| **Choir of the Storm** | `Uniques/amulet.lua` | `Trigger Level 30 Lightning Bolt when you deal a Critical Strike`, `50% increased Lightning Damage` |

## Rare Gear Priorities

- Weapon: high phys claw or sword (`Bases/claw.lua`, `sword.lua`) — 50% converts to lightning
- Gloves: attack speed, accuracy, life
- Boots: movement speed, life, resists
- Jewels: lightning damage, attack speed, crit multi

## Passive Tree

- Dexterity start → lightning and attack wheels
- Crit, attack speed, weapon elemental damage
- Chain / projectile nodes where available
- Life and resists
- **Cluster jewels** (`ClusterJewels.lua`): medium **Lightning Damage** — notable **Overshock** (`30% increased Lightning Damage`, `40% increased Effect of Lightning Ailments`)

## Pantheons (from `Pantheons.lua`)

- **Major:** Soul of Lunaris — movement speed near enemies (mapping while chaining)
- **Minor:** Soul of Ryslatha — life flask recovery

## Play Loop (from data mechanics)

1. Melee strike hits — spawns projectiles that **cannot miss** if melee connected (`description` in `act_dex.lua`).
2. Projectiles **chain** — each chain adds +10% more damage.
3. Shock enemies → kill with Inpulsa equipped → **explosion** chains clear.
4. Berek's Respite spreads shock to nearby enemies on kill.

## Defences

- Life-based attack build — stack life on rares
- Evasion from dex gear + Raider ascendancy
- Unaffected by Shock (Inpulsa mod) — safe to shock-map

## PoB Import Tip

In Path of Building, open the Calcs tab and check **both** damage parts (Melee hit vs Projectiles). Configure enemy shocked. Add chain count to see per-chain scaling from `skill_damage_+%_final_per_chain_from_skill_specific_stat`.

## Data File Index

```
src/Data/Gems.lua              → LightningStrikeAltX gem definition
src/Data/Skills/act_dex.lua    → chain scaling, conversion, damage parts
src/Data/Uniques/body.lua      → Inpulsa's Broken Heart
src/Data/Uniques/sword.lua     → Terminus Est
src/Data/Uniques/ring.lua      → Berek's Respite
src/Data/ClusterJewels.lua     → Lightning cluster notables (Overshock)
src/Modules/CalcOffence.lua    → per-part damage passes
```
